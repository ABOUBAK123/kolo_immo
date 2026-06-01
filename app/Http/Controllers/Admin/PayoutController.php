<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\BookingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PayoutController extends Controller
{
    public function __construct(protected BookingService $bookingService) {}

    /**
     * Page principale : fonds en attente de libération + résumé financier.
     */
    public function index(Request $request)
    {
        // Fonds en attente (completed + escrowed)
        $pendingQuery = Booking::where('status', 'completed')
            ->where('payment_status', 'escrowed')
            ->with(['property:id,title,city', 'owner:id,name,email', 'tenant:id,name'])
            ->latest('funds_released_at');

        if ($request->filled('search')) {
            $s = $request->search;
            $pendingQuery->where(function ($q) use ($s) {
                $q->where('reference', 'like', "%{$s}%")
                  ->orWhereHas('owner', fn($q) => $q->where('name', 'like', "%{$s}%"))
                  ->orWhereHas('property', fn($q) => $q->where('title', 'like', "%{$s}%"));
            });
        }

        $pending = $pendingQuery->paginate(20)->withQueryString();

        // Totaux en attente
        $totalEscrowed   = Booking::where('status', 'completed')->where('payment_status', 'escrowed')->sum('subtotal');
        $totalCommission = Booking::where('status', 'completed')->where('payment_status', 'escrowed')->sum('platform_commission');
        $totalOwnerDue   = $totalEscrowed - $totalCommission;
        $pendingCount    = Booking::where('status', 'completed')->where('payment_status', 'escrowed')->count();

        // Historique des virements (released)
        $released = Booking::where('payment_status', 'released')
            ->with(['property:id,title', 'owner:id,name', 'releasedBy:id,name'])
            ->orderByDesc('funds_released_at')
            ->limit(50)
            ->get();

        return view('admin.payouts.index', compact(
            'pending', 'released',
            'totalEscrowed', 'totalCommission', 'totalOwnerDue', 'pendingCount'
        ));
    }

    /**
     * Libérer les fonds d'une seule réservation.
     */
    public function release(Booking $booking)
    {
        try {
            $this->bookingService->releaseFunds($booking, Auth::id());
            return back()->with('success', "Fonds de la réservation #{$booking->reference} libérés et crédités sur le portefeuille de {$booking->owner->name}.");
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Libérer les fonds de plusieurs réservations en masse.
     */
    public function bulkRelease(Request $request)
    {
        $request->validate(['booking_ids' => ['required', 'array', 'min:1']]);

        $ids      = $request->booking_ids;
        $success  = 0;
        $errors   = [];

        $bookings = Booking::whereIn('id', $ids)
            ->where('status', 'completed')
            ->where('payment_status', 'escrowed')
            ->with(['owner', 'property'])
            ->get();

        foreach ($bookings as $booking) {
            try {
                $this->bookingService->releaseFunds($booking, Auth::id());
                $success++;
            } catch (\Throwable $e) {
                $errors[] = "#{$booking->reference} : " . $e->getMessage();
            }
        }

        $msg = "{$success} virement(s) effectué(s) avec succès.";
        if (!empty($errors)) {
            $msg .= ' Erreurs : ' . implode(' | ', $errors);
        }

        return back()->with($errors ? 'warning' : 'success', $msg);
    }
}
