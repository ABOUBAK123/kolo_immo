<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookingRequest;
use App\Models\Booking;
use App\Models\Property;
use App\Services\BookingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    public function __construct(protected BookingService $bookingService)
    {
    }

    // ─── TENANT ───────────────────────────────────────────────────────────────

    /**
     * Show the booking form for a property.
     */
    public function create(Request $request)
    {
        $propertyId = $request->get('property_id') ?? $request->get('property');
        $property   = Property::findOrFail($propertyId);

        if ($property->status !== 'active') {
            abort(404, 'Ce logement n\'est pas disponible.');
        }

        $checkIn  = $request->get('check_in');
        $checkOut = $request->get('check_out');
        $guests   = (int) $request->get('guests', 1);
        $pricing  = null;

        if ($checkIn && $checkOut) {
            try {
                $pricing = $this->bookingService->calculatePrice($property, $checkIn, $checkOut, $guests);
            } catch (\Exception $e) {
                // Invalid dates
            }
        }

        $property->load(['photos', 'amenities', 'owner']);

        return view('bookings.create', compact('property', 'checkIn', 'checkOut', 'guests', 'pricing'));
    }

    /**
     * Store a new booking.
     */
    public function store(StoreBookingRequest $request)
    {
        $property = Property::findOrFail($request->property_id);

        if ($property->status !== 'active') {
            return back()->withErrors(['property_id' => 'Ce logement n\'est pas disponible.']);
        }

        if (Auth::id() === $property->owner_id) {
            return back()->withErrors(['property_id' => 'Vous ne pouvez pas réserver votre propre logement.']);
        }

        try {
            $booking = $this->bookingService->createBooking(
                $property,
                Auth::user(),
                $request->validated()
            );
        } catch (\RuntimeException $e) {
            return back()->withErrors(['check_in' => $e->getMessage()]);
        }

        return redirect()->route('bookings.show', $booking)
            ->with('success', 'Réservation créée ! Veuillez procéder au paiement.');
    }

    /**
     * Tenant's booking list.
     */
    public function myBookings()
    {
        $bookings = Auth::user()
            ->bookingsAsTenant()
            ->with(['property.photos', 'payment'])
            ->latest()
            ->paginate(10);

        return view('bookings.my-bookings', compact('bookings'));
    }

    /**
     * Show a single booking.
     */
    public function show(Booking $booking)
    {
        $this->authorizeBookingAccess($booking);

        $booking->load(['property.photos', 'property.amenities', 'tenant', 'owner', 'payment', 'contract']);

        return view('bookings.show', compact('booking'));
    }

    /**
     * Tenant cancels a booking.
     */
    public function cancel(Request $request, Booking $booking)
    {
        if (Auth::id() !== $booking->tenant_id) {
            abort(403);
        }

        if (!$booking->isCancellable()) {
            return back()->withErrors(['error' => 'Cette réservation ne peut pas être annulée.']);
        }

        $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $this->bookingService->cancelBooking(
            $booking,
            'tenant',
            $request->reason ?? 'Annulée par le locataire'
        );

        return redirect()->route('bookings.my-bookings')
            ->with('success', 'Réservation annulée avec succès.');
    }

    // ─── OWNER ────────────────────────────────────────────────────────────────

    /**
     * Owner sees pending booking requests.
     */
    public function pendingBookings()
    {
        $bookings = Auth::user()
            ->bookingsAsOwner()
            ->pending()
            ->with(['property', 'tenant'])
            ->latest()
            ->paginate(10);

        return view('owner.bookings.pending', compact('bookings'));
    }

    /**
     * Owner's full booking list.
     */
    public function ownerBookings()
    {
        $bookings = Auth::user()
            ->bookingsAsOwner()
            ->with(['property', 'tenant', 'payment'])
            ->latest()
            ->paginate(10);

        return view('owner.bookings.index', compact('bookings'));
    }

    /**
     * Owner confirms a pending booking.
     */
    public function confirm(Booking $booking)
    {
        if (Auth::id() !== $booking->owner_id) {
            abort(403);
        }

        try {
            $this->bookingService->confirmBooking($booking);
        } catch (\RuntimeException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        return back()->with('success', 'Réservation confirmée ! Le locataire sera notifié.');
    }

    /**
     * Owner rejects a pending booking.
     */
    public function reject(Request $request, Booking $booking)
    {
        if (Auth::id() !== $booking->owner_id) {
            abort(403);
        }

        $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ], [
            'reason.required' => 'Veuillez indiquer le motif du refus.',
        ]);

        $this->bookingService->cancelBooking(
            $booking,
            'owner',
            $request->reason
        );

        return back()->with('success', 'Réservation refusée.');
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    protected function authorizeBookingAccess(Booking $booking): void
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            return;
        }

        if ($user->id !== $booking->tenant_id && $user->id !== $booking->owner_id) {
            abort(403, 'Accès non autorisé à cette réservation.');
        }
    }
}
