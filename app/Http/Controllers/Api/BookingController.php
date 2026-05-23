<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Property;
use App\Services\BookingService;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function __construct(protected BookingService $bookingService)
    {
    }

    /**
     * Create a new booking (API).
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'property_id'      => ['required', 'exists:properties,id'],
            'check_in'         => ['required', 'date', 'after_or_equal:today'],
            'check_out'        => ['required', 'date', 'after:check_in'],
            'guests'           => ['required', 'integer', 'min:1'],
            'special_requests' => ['nullable', 'string', 'max:500'],
        ]);

        $property = Property::findOrFail($data['property_id']);
        $user     = $request->user();

        if ($property->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Ce logement n\'est pas disponible.',
            ], 422);
        }

        if ($user->id === $property->owner_id) {
            return response()->json([
                'success' => false,
                'message' => 'Vous ne pouvez pas réserver votre propre logement.',
            ], 422);
        }

        if ($data['guests'] > $property->capacity) {
            return response()->json([
                'success' => false,
                'message' => "Ce logement accueille au maximum {$property->capacity} personne(s).",
            ], 422);
        }

        try {
            $booking = $this->bookingService->createBooking($property, $user, $data);
        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }

        $booking->load(['property', 'tenant', 'owner']);

        return response()->json([
            'success' => true,
            'message' => 'Réservation créée avec succès.',
            'data'    => $this->formatBooking($booking),
        ], 201);
    }

    /**
     * Get the authenticated user's bookings.
     */
    public function index(Request $request)
    {
        $user    = $request->user();
        $status  = $request->get('status');
        $asRole  = $request->get('role', 'tenant'); // 'tenant' or 'owner'

        if ($asRole === 'owner') {
            $query = $user->bookingsAsOwner();
        } else {
            $query = $user->bookingsAsTenant();
        }

        if ($status) {
            $query->where('status', $status);
        }

        $bookings = $query->with(['property.photos', 'payment'])
            ->latest()
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data'    => [
                'bookings'   => $bookings->map(fn($b) => $this->formatBooking($b)),
                'pagination' => [
                    'current_page' => $bookings->currentPage(),
                    'last_page'    => $bookings->lastPage(),
                    'total'        => $bookings->total(),
                ],
            ],
        ]);
    }

    /**
     * Get a single booking detail.
     */
    public function show(Request $request, Booking $booking)
    {
        $user = $request->user();

        if ($user->id !== $booking->tenant_id && $user->id !== $booking->owner_id && !$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Accès non autorisé.',
            ], 403);
        }

        $booking->load(['property.photos', 'tenant', 'owner', 'payment', 'contract']);

        return response()->json([
            'success' => true,
            'data'    => $this->formatBooking($booking),
        ]);
    }

    /**
     * Owner confirms a pending booking.
     */
    public function confirm(Request $request, Booking $booking)
    {
        $user = $request->user();

        if ($user->id !== $booking->owner_id) {
            return response()->json([
                'success' => false,
                'message' => 'Seul le propriétaire peut confirmer cette réservation.',
            ], 403);
        }

        if (!$booking->isPending()) {
            return response()->json([
                'success' => false,
                'message' => 'Cette réservation ne peut pas être confirmée.',
            ], 422);
        }

        try {
            $booking = $this->bookingService->confirmBooking($booking);
        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Réservation confirmée.',
            'data'    => $this->formatBooking($booking),
        ]);
    }

    /**
     * Owner rejects a pending booking.
     */
    public function reject(Request $request, Booking $booking)
    {
        $user = $request->user();

        if ($user->id !== $booking->owner_id) {
            return response()->json([
                'success' => false,
                'message' => 'Seul le propriétaire peut rejeter cette réservation.',
            ], 403);
        }

        $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $booking = $this->bookingService->cancelBooking(
                $booking,
                'owner',
                $request->reason ?? 'Réservation refusée par le propriétaire'
            );
        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Réservation refusée.',
            'data'    => $this->formatBooking($booking),
        ]);
    }

    /**
     * Cancel a booking.
     */
    public function cancel(Request $request, Booking $booking)
    {
        $user = $request->user();

        if ($user->id !== $booking->tenant_id && $user->id !== $booking->owner_id) {
            return response()->json([
                'success' => false,
                'message' => 'Accès non autorisé.',
            ], 403);
        }

        if (!$booking->isCancellable()) {
            return response()->json([
                'success' => false,
                'message' => 'Cette réservation ne peut pas être annulée.',
            ], 422);
        }

        $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $cancelledBy = $user->id === $booking->tenant_id ? 'tenant' : 'owner';

        $booking = $this->bookingService->cancelBooking(
            $booking,
            $cancelledBy,
            $request->reason ?? 'Annulée via l\'application'
        );

        return response()->json([
            'success' => true,
            'message' => 'Réservation annulée avec succès.',
            'data'    => $this->formatBooking($booking),
        ]);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    protected function formatBooking(Booking $booking): array
    {
        $data = [
            'id'                  => $booking->id,
            'reference'           => $booking->reference,
            'property_id'         => $booking->property_id,
            'check_in'            => $booking->check_in?->toDateString(),
            'check_out'           => $booking->check_out?->toDateString(),
            'nights'              => $booking->nights,
            'guests'              => $booking->guests,
            'price_per_night'     => $booking->price_per_night,
            'subtotal'            => $booking->subtotal,
            'service_fee'         => $booking->service_fee,
            'deposit_amount'      => $booking->deposit_amount,
            'total_amount'        => $booking->total_amount,
            'status'              => $booking->status,
            'status_label'        => $booking->statusLabel(),
            'payment_status'      => $booking->payment_status,
            'special_requests'    => $booking->special_requests,
            'confirmed_at'        => $booking->confirmed_at?->toIso8601String(),
            'cancelled_at'        => $booking->cancelled_at?->toIso8601String(),
            'cancellation_reason' => $booking->cancellation_reason,
            'created_at'          => $booking->created_at?->toIso8601String(),
        ];

        if ($booking->relationLoaded('property') && $booking->property) {
            $data['property'] = [
                'id'              => $booking->property->id,
                'title'           => $booking->property->title,
                'city'            => $booking->property->city,
                'cover_photo_url' => $booking->property->coverPhotoUrl(),
            ];
        }

        if ($booking->relationLoaded('payment') && $booking->payment) {
            $data['payment'] = [
                'id'     => $booking->payment->id,
                'status' => $booking->payment->status,
                'method' => $booking->payment->methodLabel(),
                'amount' => $booking->payment->amount,
            ];
        }

        return $data;
    }
}
