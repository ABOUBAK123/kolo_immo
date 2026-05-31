<?php

namespace App\Http\Controllers;

use App\Mail\RenewalRequestMail;
use App\Models\Booking;
use App\Models\ContractRenewal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class RenewalController extends Controller
{
    /**
     * Show the renewal request form.
     */
    public function create(Booking $booking)
    {
        $user = Auth::user();

        if ($user->id !== $booking->owner_id && $user->id !== $booking->tenant_id) {
            abort(403);
        }

        if (!in_array($booking->status, ['confirmed', 'completed'])) {
            return back()->with('error', 'Impossible de demander un renouvellement pour cette réservation.');
        }

        if ($booking->pendingRenewal) {
            return redirect()->route('renewals.show', $booking->pendingRenewal)
                ->with('info', 'Une demande de renouvellement est déjà en cours.');
        }

        $booking->load(['property', 'tenant', 'owner']);
        return view('renewals.create', compact('booking'));
    }

    /**
     * Store a new renewal request.
     */
    public function store(Request $request, Booking $booking)
    {
        $user = Auth::user();

        if ($user->id !== $booking->owner_id && $user->id !== $booking->tenant_id) {
            abort(403);
        }

        $data = $request->validate([
            'proposed_amount'   => ['required', 'numeric', 'min:0'],
            'additional_nights' => ['required', 'integer', 'min:1', 'max:365'],
            'note'              => ['nullable', 'string', 'max:500'],
        ], [
            'proposed_amount.required'   => 'Le montant proposé est obligatoire.',
            'additional_nights.required' => 'La durée supplémentaire est obligatoire.',
            'additional_nights.min'      => 'Minimum 1 nuit supplémentaire.',
        ]);

        $currentEnd   = $booking->check_out;
        $proposedEnd  = $currentEnd->copy()->addDays($data['additional_nights']);

        $renewal = ContractRenewal::create([
            'booking_id'        => $booking->id,
            'initiated_by'      => $user->id,
            'status'            => 'pending',
            'current_amount'    => $booking->price_per_night,
            'proposed_amount'   => $data['proposed_amount'],
            'current_end_date'  => $currentEnd,
            'proposed_end_date' => $proposedEnd,
            'additional_nights' => $data['additional_nights'],
            'note'              => $data['note'],
        ]);

        // Notify the other party
        $recipient = $user->id === $booking->owner_id ? $booking->tenant : $booking->owner;
        if ($recipient->email) {
            Mail::to($recipient->email)->queue(new RenewalRequestMail($renewal, $booking, $recipient));
        }

        return redirect()->route('renewals.show', $renewal)
            ->with('success', 'Demande de renouvellement envoyée. La partie adverse sera notifiée.');
    }

    /**
     * Show a renewal request.
     */
    public function show(ContractRenewal $renewal)
    {
        $user = Auth::user();
        $booking = $renewal->booking()->with(['property', 'tenant', 'owner'])->first();

        if ($user->id !== $booking->owner_id && $user->id !== $booking->tenant_id) {
            abort(403);
        }

        return view('renewals.show', compact('renewal', 'booking'));
    }

    /**
     * Accept a renewal request (extends the booking).
     */
    public function accept(Request $request, ContractRenewal $renewal)
    {
        $user    = Auth::user();
        $booking = $renewal->booking;

        if ($user->id !== $booking->owner_id && $user->id !== $booking->tenant_id) {
            abort(403);
        }

        if ($user->id === $renewal->initiated_by) {
            return back()->with('error', 'Vous ne pouvez pas accepter votre propre demande.');
        }

        if (!$renewal->isPending()) {
            return back()->with('error', 'Cette demande n\'est plus en attente.');
        }

        DB::transaction(function () use ($renewal, $booking, $user) {
            // Extend booking
            $booking->update([
                'check_out' => $renewal->proposed_end_date,
                'nights'    => $booking->check_in->diffInDays($renewal->proposed_end_date),
                'price_per_night' => $renewal->proposed_amount,
                'status'    => 'confirmed',
            ]);

            $renewal->update([
                'status'       => 'accepted',
                'responded_at' => now(),
                'responded_by' => $user->id,
            ]);
        });

        return redirect()->route('bookings.show', $booking)
            ->with('success', 'Renouvellement accepté ! Le séjour est prolongé jusqu\'au ' . $renewal->proposed_end_date->format('d/m/Y') . '.');
    }

    /**
     * Reject a renewal request.
     */
    public function reject(Request $request, ContractRenewal $renewal)
    {
        $user    = Auth::user();
        $booking = $renewal->booking;

        if ($user->id !== $booking->owner_id && $user->id !== $booking->tenant_id) {
            abort(403);
        }

        if ($user->id === $renewal->initiated_by) {
            return back()->with('error', 'Vous ne pouvez pas refuser votre propre demande.');
        }

        if (!$renewal->isPending()) {
            return back()->with('error', 'Cette demande n\'est plus en attente.');
        }

        $renewal->update([
            'status'       => 'rejected',
            'responded_at' => now(),
            'responded_by' => $user->id,
        ]);

        return redirect()->route('bookings.show', $booking)
            ->with('info', 'Demande de renouvellement refusée.');
    }

    /**
     * Submit a termination notice.
     */
    public function terminate(Request $request, Booking $booking)
    {
        $user = Auth::user();

        if ($user->id !== $booking->owner_id && $user->id !== $booking->tenant_id) {
            abort(403);
        }

        if (!in_array($booking->status, ['confirmed'])) {
            return back()->with('error', 'Impossible de résilier cette réservation.');
        }

        $data = $request->validate([
            'termination_reason' => ['required', 'string', 'min:20', 'max:500'],
            'notice_days'        => ['required', 'integer', 'in:30,60'],
        ]);

        $noticeAt       = now();
        $terminationDate = $noticeAt->copy()->addDays((int) $data['notice_days']);

        // Ensure termination date is before check_out
        if ($booking->check_out && $terminationDate->gt($booking->check_out)) {
            $terminationDate = $booking->check_out;
        }

        $booking->update([
            'termination_notice_at' => $noticeAt,
            'termination_date'      => $terminationDate,
            'termination_reason'    => $data['termination_reason'],
            'termination_by'        => $user->id,
        ]);

        return redirect()->route('bookings.show', $booking)
            ->with('success', "Préavis de résiliation enregistré. La réservation prendra fin le {$terminationDate->format('d/m/Y')} ({$data['notice_days']} jours de préavis).");
    }
}
