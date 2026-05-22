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

    // ─── Helpers ──────────────────────────────────────────────────────────────

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
