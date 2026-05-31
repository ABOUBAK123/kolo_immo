<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dispute;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DisputeController extends Controller
{
    public function index(Request $request)
    {
        $query = Dispute::with(['booking.property', 'openedBy', 'againstUser'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $disputes = $query->paginate(20)->withQueryString();
        $counts   = [
            'open'        => Dispute::where('status', 'open')->count(),
            'in_progress' => Dispute::where('status', 'in_progress')->count(),
            'resolved'    => Dispute::whereIn('status', ['resolved', 'closed'])->count(),
        ];

        return view('admin.disputes.index', compact('disputes', 'counts'));
    }

    public function show(Dispute $dispute)
    {
        $dispute->load([
            'booking.property', 'booking.tenant', 'booking.owner',
            'openedBy', 'againstUser', 'evidences.uploader', 'resolvedBy',
        ]);

        $statuses   = Dispute::STATUSES;
        $resolutions= Dispute::RESOLUTIONS;

        return view('admin.disputes.show', compact('dispute', 'statuses', 'resolutions'));
    }

    public function updateStatus(Request $request, Dispute $dispute)
    {
        $request->validate([
            'status'      => ['required', 'in:open,in_progress,resolved,closed'],
            'admin_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $dispute->update([
            'status'      => $request->status,
            'admin_notes' => $request->admin_notes ?? $dispute->admin_notes,
        ]);

        return back()->with('success', 'Statut du litige mis à jour.');
    }

    public function resolve(Request $request, Dispute $dispute)
    {
        $request->validate([
            'resolution'  => ['required', 'in:refund_full,refund_partial,no_action'],
            'admin_notes' => ['required', 'string', 'min:20', 'max:2000'],
        ], [
            'resolution.required'  => 'Veuillez choisir une décision.',
            'admin_notes.required' => 'Une note de résolution est obligatoire.',
            'admin_notes.min'      => 'La note doit contenir au moins 20 caractères.',
        ]);

        $dispute->update([
            'status'      => 'resolved',
            'resolution'  => $request->resolution,
            'admin_notes' => $request->admin_notes,
            'resolved_by' => Auth::id(),
            'resolved_at' => now(),
        ]);

        // Update booking status based on resolution
        $bookingStatus = match($request->resolution) {
            'refund_full', 'refund_partial' => 'refund_pending',
            default                         => 'completed',
        };
        $dispute->booking->update(['status' => $bookingStatus]);

        return back()->with('success', 'Litige résolu avec succès.');
    }
}
