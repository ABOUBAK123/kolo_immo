<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Dispute;
use App\Models\DisputeEvidence;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DisputeController extends Controller
{
    /**
     * Show the dispute creation form for a given booking.
     */
    public function create(Booking $booking)
    {
        $user = Auth::user();

        if ($user->id !== $booking->tenant_id && $user->id !== $booking->owner_id) {
            abort(403);
        }

        if (!in_array($booking->status, ['confirmed', 'completed', 'refund_pending'])) {
            return redirect()->route('bookings.show', $booking)
                ->with('error', 'Un litige ne peut être ouvert que sur une réservation confirmée ou terminée.');
        }

        // One dispute per booking per user
        $existing = Dispute::where('booking_id', $booking->id)
            ->where('opened_by', $user->id)
            ->first();

        if ($existing) {
            return redirect()->route('disputes.show', $existing)
                ->with('info', 'Vous avez déjà un litige ouvert pour cette réservation.');
        }

        $booking->load(['property', 'tenant', 'owner']);
        $reasons = Dispute::REASONS;

        return view('disputes.create', compact('booking', 'reasons'));
    }

    /**
     * Store a new dispute with optional evidence files.
     */
    public function store(Request $request, Booking $booking)
    {
        $user = Auth::user();

        if ($user->id !== $booking->tenant_id && $user->id !== $booking->owner_id) {
            abort(403);
        }

        if (!in_array($booking->status, ['confirmed', 'completed', 'refund_pending'])) {
            return back()->with('error', 'Impossible d\'ouvrir un litige pour cette réservation.');
        }

        $data = $request->validate([
            'reason'      => ['required', 'in:' . implode(',', array_keys(Dispute::REASONS))],
            'description' => ['required', 'string', 'min:50', 'max:3000'],
            'evidences.*' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf,doc,docx', 'max:5120'],
        ], [
            'reason.required'      => 'Veuillez choisir un motif.',
            'description.required' => 'La description est obligatoire.',
            'description.min'      => 'La description doit contenir au moins 50 caractères.',
            'evidences.*.mimes'    => 'Fichiers acceptés : images, PDF, Word.',
            'evidences.*.max'      => 'Chaque fichier ne peut dépasser 5 Mo.',
        ]);

        $againstUserId = $user->id === $booking->tenant_id
            ? $booking->owner_id
            : $booking->tenant_id;

        $dispute = Dispute::create([
            'booking_id'     => $booking->id,
            'opened_by'      => $user->id,
            'against_user_id'=> $againstUserId,
            'reason'         => $data['reason'],
            'description'    => $data['description'],
            'status'         => 'open',
        ]);

        // Save evidence files
        if ($request->hasFile('evidences')) {
            foreach ($request->file('evidences') as $file) {
                $path = $file->store('disputes/' . $dispute->id, 'public');
                DisputeEvidence::create([
                    'dispute_id'    => $dispute->id,
                    'uploader_id'   => $user->id,
                    'file_path'     => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type'     => $file->getMimeType(),
                    'file_size'     => $file->getSize(),
                ]);
            }
        }

        // Flag the booking
        $booking->update(['status' => 'disputed']);

        return redirect()->route('disputes.show', $dispute)
            ->with('success', 'Votre litige a bien été ouvert. L\'équipe Kolo Immo va examiner votre dossier sous 48h.');
    }

    /**
     * Show a dispute (only accessible to the parties involved).
     */
    public function show(Dispute $dispute)
    {
        $user = Auth::user();

        if ($user->id !== $dispute->opened_by && $user->id !== $dispute->against_user_id) {
            abort(403);
        }

        $dispute->load(['booking.property', 'openedBy', 'againstUser', 'evidences.uploader']);

        return view('disputes.show', compact('dispute'));
    }
}
