<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Property;
use App\Models\Review;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    /**
     * List reviews for a property (public).
     */
    public function indexProperty(Property $property, Request $request): JsonResponse
    {
        $perPage = min((int) $request->get('per_page', 5), 50);

        $reviews = Review::forProperty($property->id)
            ->where('is_flagged', false)
            ->with(['reviewer:id,name,avatar', 'property:id,title'])
            ->latest()
            ->paginate($perPage);

        return response()->json([
            'data' => $reviews->map(fn($r) => $this->formatReview($r)),
            'meta' => [
                'current_page' => $reviews->currentPage(),
                'last_page'    => $reviews->lastPage(),
                'total'        => $reviews->total(),
                'per_page'     => $reviews->perPage(),
            ],
        ]);
    }

    /**
     * Get a single review (public).
     */
    public function show(Review $review): JsonResponse
    {
        if ($review->is_flagged) {
            return response()->json(['error' => 'Review not found'], 404);
        }

        $review->load(['reviewer:id,name,avatar', 'property:id,title', 'booking:id']);

        return response()->json(['data' => $this->formatReview($review)]);
    }

    /**
     * Create a review (authenticated - tenant or owner).
     */
    public function store(Request $request): JsonResponse
    {
        $user = Auth::user();
        $booking = Booking::findOrFail($request->input('booking_id'));

        // Determine review type and validate permissions
        $type = $request->input('type'); // 'tenant_to_property' or 'owner_to_tenant'

        if ($type === 'tenant_to_property') {
            if ($user->id !== $booking->tenant_id || $booking->status !== 'completed') {
                return response()->json(['error' => 'Cannot review this booking'], 403);
            }

            $rules = [
                'type'                 => ['required', 'in:tenant_to_property'],
                'rating_overall'       => ['required', 'integer', 'min:1', 'max:5'],
                'rating_cleanliness'   => ['required', 'integer', 'min:1', 'max:5'],
                'rating_communication' => ['required', 'integer', 'min:1', 'max:5'],
                'rating_accuracy'      => ['required', 'integer', 'min:1', 'max:5'],
                'rating_location'      => ['required', 'integer', 'min:1', 'max:5'],
                'rating_value'         => ['required', 'integer', 'min:1', 'max:5'],
                'comment'              => ['required', 'string', 'min:20', 'max:1000'],
            ];
        } elseif ($type === 'owner_to_tenant') {
            if ($user->id !== $booking->owner_id || $booking->status !== 'completed') {
                return response()->json(['error' => 'Cannot review this booking'], 403);
            }

            $rules = [
                'type'                 => ['required', 'in:owner_to_tenant'],
                'rating_overall'       => ['required', 'integer', 'min:1', 'max:5'],
                'rating_cleanliness'   => ['required', 'integer', 'min:1', 'max:5'],
                'rating_communication' => ['required', 'integer', 'min:1', 'max:5'],
                'rating_payment'       => ['required', 'integer', 'min:1', 'max:5'],
                'comment'              => ['required', 'string', 'min:20', 'max:1000'],
            ];
        } else {
            return response()->json(['error' => 'Invalid review type'], 422);
        }

        $data = $request->validate($rules);

        // Check for duplicate review
        $exists = Review::where('booking_id', $booking->id)
            ->where('reviewer_id', $user->id)
            ->where('type', $type)
            ->exists();

        if ($exists) {
            return response()->json(['error' => 'Review already exists for this booking'], 409);
        }

        // Create review
        $review = Review::create([
            'booking_id'    => $booking->id,
            'reviewer_id'   => $user->id,
            'reviewee_id'   => $type === 'tenant_to_property' ? $booking->owner_id : $booking->tenant_id,
            'property_id'   => $booking->property_id,
            'type'          => $type,
            ...$data,
        ]);

        // Update aggregates
        if ($type === 'tenant_to_property') {
            $this->updatePropertyRating($booking->property_id);
        } else {
            $this->updateTenantTrustScore($booking->tenant_id);
        }

        return response()->json(['data' => $this->formatReview($review)], 201);
    }

    /**
     * Owner replies to a review (authenticated).
     */
    public function reply(Request $request, Review $review): JsonResponse
    {
        $user = Auth::user();

        if ($user->id !== $review->property->owner_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $data = $request->validate([
            'owner_reply' => ['required', 'string', 'min:10', 'max:500'],
        ]);

        $review->update([
            'owner_reply'      => $data['owner_reply'],
            'owner_replied_at' => now(),
        ]);

        return response()->json(['data' => $this->formatReview($review)]);
    }

    /**
     * Flag a review for moderation (authenticated).
     */
    public function flag(Request $request, Review $review): JsonResponse
    {
        $user = Auth::user();

        if ($review->reviewer_id === $user->id) {
            return response()->json(['error' => 'Cannot flag your own review'], 422);
        }

        $data = $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $review->update([
            'is_flagged'  => true,
            'flag_reason' => $data['reason'],
        ]);

        return response()->json(['message' => 'Review flagged for moderation']);
    }

    /**
     * Get user trust score (public).
     */
    public function trustScore(User $user): JsonResponse
    {
        return response()->json([
            'data' => [
                'user_id'     => $user->id,
                'user_name'   => $user->name,
                'trust_score' => round($user->trust_score ?? 0, 2),
                'max_score'   => 100,
            ],
        ]);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    protected function formatReview(Review $review): array
    {
        return [
            'id'                   => $review->id,
            'type'                 => $review->type,
            'reviewer'             => [
                'id'     => $review->reviewer->id,
                'name'   => $review->reviewer->name,
                'avatar' => $review->reviewer->avatar,
            ],
            'property'             => [
                'id'    => $review->property->id,
                'title' => $review->property->title,
            ],
            'ratings'              => [
                'overall'       => (float) $review->rating_overall,
                'cleanliness'   => (float) $review->rating_cleanliness,
                'communication' => (float) $review->rating_communication,
                'accuracy'      => (float) $review->rating_accuracy,
                'location'      => (float) $review->rating_location,
                'value'         => (float) $review->rating_value,
                'payment'       => (float) ($review->rating_payment ?? 0),
                'average'       => (float) $review->averageRating(),
            ],
            'comment'              => $review->comment,
            'owner_reply'          => $review->owner_reply,
            'owner_replied_at'     => $review->owner_replied_at?->toIso8601String(),
            'is_flagged'           => $review->is_flagged,
            'created_at'           => $review->created_at->toIso8601String(),
        ];
    }

    protected function updatePropertyRating(int $propertyId): void
    {
        $stats = Review::forProperty($propertyId)
            ->selectRaw('AVG(rating_overall) as avg_rating, COUNT(*) as cnt')
            ->first();

        if (!$stats || $stats->cnt === 0) {
            return;
        }

        Property::find($propertyId)?->update([
            'rating_avg'   => round($stats->avg_rating, 1),
            'rating_count' => $stats->cnt,
        ]);
    }

    protected function updateTenantTrustScore(int $tenantId): void
    {
        $stats = Review::where('reviewee_id', $tenantId)
            ->where('type', 'owner_to_tenant')
            ->selectRaw('AVG(rating_overall) as avg_rating, COUNT(*) as cnt')
            ->first();

        if (!$stats || $stats->cnt === 0) {
            return;
        }

        $score = round(($stats->avg_rating / 5) * 100, 2);
        User::find($tenantId)?->update(['trust_score' => max(0, min(100, $score))]);
    }
}
