<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Property;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    /**
     * Show the review creation form (after a completed booking).
     */
    public function create(Booking $booking)
    {
        $user = Auth::user();

        // Only the tenant can review after a completed booking
        if ($user->id !== $booking->tenant_id) {
            abort(403);
        }

        if ($booking->status !== 'completed') {
            return redirect()->route('bookings.show', $booking)
                ->with('error', 'Vous ne pouvez laisser un avis qu\'après la fin de votre séjour.');
        }

        // Check if review already exists
        $existingReview = Review::where('booking_id', $booking->id)
            ->where('reviewer_id', $user->id)
            ->where('type', 'tenant_to_property')
            ->first();

        if ($existingReview) {
            return redirect()->route('properties.show', $booking->property_id)
                ->with('info', 'Vous avez déjà laissé un avis pour ce séjour.');
        }

        $booking->load(['property.photos', 'owner']);

        return view('reviews.create', compact('booking'));
    }

    /**
     * Store a new review.
     */
    public function store(Request $request, Booking $booking)
    {
        $user = Auth::user();

        if ($user->id !== $booking->tenant_id) {
            abort(403);
        }

        if ($booking->status !== 'completed') {
            return back()->with('error', 'Vous ne pouvez laisser un avis qu\'après la fin de votre séjour.');
        }

        // Prevent duplicate reviews
        $existing = Review::where('booking_id', $booking->id)
            ->where('reviewer_id', $user->id)
            ->where('type', 'tenant_to_property')
            ->first();

        if ($existing) {
            return redirect()->route('properties.show', $booking->property_id)
                ->with('info', 'Vous avez déjà laissé un avis pour ce séjour.');
        }

        $data = $request->validate([
            'rating_overall'       => ['required', 'integer', 'min:1', 'max:5'],
            'rating_cleanliness'   => ['required', 'integer', 'min:1', 'max:5'],
            'rating_communication' => ['required', 'integer', 'min:1', 'max:5'],
            'rating_accuracy'      => ['required', 'integer', 'min:1', 'max:5'],
            'rating_location'      => ['required', 'integer', 'min:1', 'max:5'],
            'rating_value'         => ['required', 'integer', 'min:1', 'max:5'],
            'comment'              => ['required', 'string', 'min:20', 'max:1000'],
        ], [
            'rating_overall.required'       => 'La note générale est obligatoire.',
            'rating_cleanliness.required'   => 'La note de propreté est obligatoire.',
            'rating_communication.required' => 'La note de communication est obligatoire.',
            'rating_accuracy.required'      => 'La note de conformité est obligatoire.',
            'rating_location.required'      => 'La note d\'emplacement est obligatoire.',
            'rating_value.required'         => 'La note de rapport qualité-prix est obligatoire.',
            'comment.required'              => 'Un commentaire est obligatoire.',
            'comment.min'                   => 'Le commentaire doit contenir au moins 20 caractères.',
        ]);

        $review = Review::create([
            'booking_id'           => $booking->id,
            'reviewer_id'          => $user->id,
            'reviewee_id'          => $booking->owner_id,
            'property_id'          => $booking->property_id,
            'type'                 => 'tenant_to_property',
            'rating_overall'       => $data['rating_overall'],
            'rating_cleanliness'   => $data['rating_cleanliness'],
            'rating_communication' => $data['rating_communication'],
            'rating_accuracy'      => $data['rating_accuracy'],
            'rating_location'      => $data['rating_location'],
            'rating_value'         => $data['rating_value'],
            'comment'              => $data['comment'],
        ]);

        // Update property rating average
        $this->updatePropertyRating($booking->property_id);

        return redirect()->route('properties.show', $booking->property_id)
            ->with('success', 'Merci pour votre avis ! Il a bien été publié.');
    }

    /**
     * Owner replies to a review.
     */
    public function reply(Request $request, Review $review)
    {
        $user = Auth::user();

        // Only the owner of the property can reply
        if ($user->id !== $review->property->owner_id) {
            abort(403);
        }

        $request->validate([
            'owner_reply' => ['required', 'string', 'min:10', 'max:500'],
        ], [
            'owner_reply.required' => 'La réponse est obligatoire.',
            'owner_reply.min'      => 'La réponse doit contenir au moins 10 caractères.',
        ]);

        $review->update([
            'owner_reply'      => $request->owner_reply,
            'owner_replied_at' => now(),
        ]);

        return back()->with('success', 'Votre réponse a bien été publiée.');
    }

    // ─── Owner → Tenant review ────────────────────────────────────────────────

    /**
     * Show the form for an owner to rate a tenant after a completed booking.
     */
    public function createOwnerReview(Booking $booking)
    {
        $user = Auth::user();

        if ($user->id !== $booking->owner_id) {
            abort(403);
        }

        if ($booking->status !== 'completed') {
            return redirect()->route('owner.bookings.index')
                ->with('error', 'Vous ne pouvez évaluer un locataire qu\'après la fin du séjour.');
        }

        $existing = Review::where('booking_id', $booking->id)
            ->where('reviewer_id', $user->id)
            ->where('type', 'owner_to_tenant')
            ->first();

        if ($existing) {
            return redirect()->route('owner.bookings.index')
                ->with('info', 'Vous avez déjà évalué ce locataire pour ce séjour.');
        }

        $booking->load(['tenant', 'property']);

        return view('reviews.create-owner', compact('booking'));
    }

    /**
     * Store an owner → tenant review and update the tenant's trust_score.
     */
    public function storeOwnerReview(Request $request, Booking $booking)
    {
        $user = Auth::user();

        if ($user->id !== $booking->owner_id) {
            abort(403);
        }

        if ($booking->status !== 'completed') {
            return back()->with('error', 'Vous ne pouvez évaluer un locataire qu\'après la fin du séjour.');
        }

        $existing = Review::where('booking_id', $booking->id)
            ->where('reviewer_id', $user->id)
            ->where('type', 'owner_to_tenant')
            ->first();

        if ($existing) {
            return redirect()->route('owner.bookings.index')
                ->with('info', 'Vous avez déjà évalué ce locataire pour ce séjour.');
        }

        $data = $request->validate([
            'rating_overall'       => ['required', 'integer', 'min:1', 'max:5'],
            'rating_cleanliness'   => ['required', 'integer', 'min:1', 'max:5'],
            'rating_communication' => ['required', 'integer', 'min:1', 'max:5'],
            'rating_payment'       => ['required', 'integer', 'min:1', 'max:5'],
            'comment'              => ['required', 'string', 'min:20', 'max:1000'],
        ], [
            'rating_overall.required'       => 'La note générale est obligatoire.',
            'rating_cleanliness.required'   => 'La note de soin du logement est obligatoire.',
            'rating_communication.required' => 'La note de communication est obligatoire.',
            'rating_payment.required'       => 'La note de ponctualité de paiement est obligatoire.',
            'comment.required'              => 'Un commentaire est obligatoire.',
            'comment.min'                   => 'Le commentaire doit contenir au moins 20 caractères.',
        ]);

        Review::create([
            'booking_id'           => $booking->id,
            'reviewer_id'          => $user->id,
            'reviewee_id'          => $booking->tenant_id,
            'property_id'          => $booking->property_id,
            'type'                 => 'owner_to_tenant',
            'rating_overall'       => $data['rating_overall'],
            'rating_cleanliness'   => $data['rating_cleanliness'],
            'rating_communication' => $data['rating_communication'],
            'rating_payment'       => $data['rating_payment'],
            'comment'              => $data['comment'],
        ]);

        // Recalculate tenant trust_score (0-100 scale, avg of owner_to_tenant reviews × 20)
        $this->updateTenantTrustScore($booking->tenant_id);

        return redirect()->route('owner.bookings.index')
            ->with('success', 'Merci ! Votre évaluation du locataire a bien été enregistrée.');
    }

    /**
     * Flag a review as inappropriate.
     */
    public function flag(Request $request, Review $review)
    {
        if ($review->reviewer_id === Auth::id()) {
            return back()->with('error', 'Vous ne pouvez pas signaler votre propre avis.');
        }

        $data = $request->validate([
            'flag_reason' => ['required', 'string', 'max:500'],
        ], [
            'flag_reason.required' => 'Veuillez indiquer le motif du signalement.',
        ]);

        $review->update([
            'is_flagged'  => true,
            'flag_reason' => $data['flag_reason'],
        ]);

        return back()->with('success', 'Avis signalé. Notre équipe va l\'examiner.');
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    protected function updateTenantTrustScore(int $tenantId): void
    {
        $stats = Review::where('reviewee_id', $tenantId)
            ->where('type', 'owner_to_tenant')
            ->selectRaw('AVG(rating_overall) as avg_rating, COUNT(*) as cnt')
            ->first();

        if (!$stats || $stats->cnt === 0) {
            return;
        }

        // Convert 1-5 scale → 0-100, capped between 20 and 100
        $score = (int) round(($stats->avg_rating / 5) * 100);
        $score = max(20, min(100, $score));

        \App\Models\User::where('id', $tenantId)->update(['trust_score' => $score]);
    }

    protected function updatePropertyRating(int $propertyId): void
    {
        $stats = Review::where('property_id', $propertyId)
            ->where('type', 'tenant_to_property')
            ->selectRaw('AVG(rating_overall) as avg_rating, COUNT(*) as count')
            ->first();

        Property::where('id', $propertyId)->update([
            'rating_avg'   => round($stats->avg_rating ?? 0, 1),
            'rating_count' => $stats->count ?? 0,
        ]);
    }
}
