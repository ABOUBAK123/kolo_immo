<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\Review;
use App\Models\User;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    /**
     * List flagged reviews for moderation.
     */
    public function index(Request $request)
    {
        $query = Review::with(['reviewer:id,name,email', 'reviewee:id,name', 'property:id,title'])
            ->where('is_flagged', true);

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $flaggedReviews = $query->latest()->paginate(20)->withQueryString();

        $stats = [
            'flagged_total'            => Review::where('is_flagged', true)->count(),
            'tenant_to_property_count' => Review::where('is_flagged', true)->where('type', 'tenant_to_property')->count(),
            'owner_to_tenant_count'    => Review::where('is_flagged', true)->where('type', 'owner_to_tenant')->count(),
        ];

        return view('admin.reviews.index', compact('flaggedReviews', 'stats'));
    }

    /**
     * Approve a flagged review (remove flag).
     */
    public function approve(Review $review)
    {
        $review->update([
            'is_flagged'  => false,
            'flag_reason' => null,
        ]);

        return back()->with('success', 'Avis approuvé et remis en ligne.');
    }

    /**
     * Delete a flagged review permanently.
     */
    public function destroy(Review $review)
    {
        $propertyId = $review->property_id;
        $tenantId   = $review->reviewee_id;
        $type       = $review->type;

        $review->delete();

        // Recalculate aggregates after deletion
        if ($type === 'tenant_to_property' && $propertyId) {
            $this->updatePropertyRating($propertyId);
        } elseif ($type === 'owner_to_tenant' && $tenantId) {
            $this->updateTenantTrustScore($tenantId);
        }

        return back()->with('success', 'Avis supprimé définitivement.');
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function updatePropertyRating(int $propertyId): void
    {
        $stats = Review::where('property_id', $propertyId)
            ->where('type', 'tenant_to_property')
            ->where('is_flagged', false)
            ->selectRaw('AVG(rating_overall) as avg_rating, COUNT(*) as cnt')
            ->first();

        Property::where('id', $propertyId)->update([
            'rating_avg'   => round($stats->avg_rating ?? 0, 1),
            'rating_count' => $stats->cnt ?? 0,
        ]);
    }

    private function updateTenantTrustScore(int $tenantId): void
    {
        $stats = Review::where('reviewee_id', $tenantId)
            ->where('type', 'owner_to_tenant')
            ->where('is_flagged', false)
            ->selectRaw('AVG(rating_overall) as avg_rating, COUNT(*) as cnt')
            ->first();

        if (!$stats || $stats->cnt === 0) {
            return;
        }

        $score = round(($stats->avg_rating / 5) * 100, 2);
        User::where('id', $tenantId)->update(['trust_score' => max(0, min(100, $score))]);
    }
}
