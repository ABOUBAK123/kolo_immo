<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\PropertyAmenity;
use Illuminate\Http\Request;

class PropertyController extends Controller
{
    /**
     * Search and list properties (API).
     */
    public function index(Request $request)
    {
        $query = Property::active()
            ->with(['photos', 'amenities', 'owner'])
            ->withCount('reviews');

        if ($request->filled('city')) {
            $query->byCity($request->city);
        }

        if ($request->filled('type')) {
            $query->byType($request->type);
        }

        $isDate = fn(?string $v) => $v && preg_match('/^\d{4}-\d{2}-\d{2}$/', $v);
        if ($isDate($request->check_in) && $isDate($request->check_out)) {
            $query->availableBetween($request->check_in, $request->check_out);
        }

        if ($request->filled('price_min') || $request->filled('price_max')) {
            $query->byPriceRange(
                $request->filled('price_min') ? (float) $request->price_min : null,
                $request->filled('price_max') ? (float) $request->price_max : null
            );
        }

        if ($request->filled('guests')) {
            $query->where('capacity', '>=', (int) $request->guests);
        }

        if ($request->filled('amenities')) {
            $amenities = is_array($request->amenities)
                ? $request->amenities
                : explode(',', $request->amenities);

            foreach ($amenities as $amenity) {
                $query->whereHas('amenities', fn($q) => $q->where('amenity', $amenity));
            }
        }

        $sortBy = $request->get('sort', 'newest');
        $sortMap = [
            'price_asc'  => ['price_per_night', 'asc'],
            'price_desc' => ['price_per_night', 'desc'],
            'rating'     => ['rating_avg', 'desc'],
            'newest'     => ['created_at', 'desc'],
            'popular'    => ['views_count', 'desc'],
        ];

        [$column, $direction] = $sortMap[$sortBy] ?? ['created_at', 'desc'];
        $query->orderBy($column, $direction);

        $perPage = min((int) $request->get('per_page', 12), 50);
        $properties = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data'    => [
                'properties' => $properties->map(fn($p) => $this->formatProperty($p)),
                'pagination' => [
                    'current_page' => $properties->currentPage(),
                    'last_page'    => $properties->lastPage(),
                    'per_page'     => $properties->perPage(),
                    'total'        => $properties->total(),
                ],
            ],
        ]);
    }

    /**
     * Get a single property detail.
     */
    public function show(Property $property)
    {
        if ($property->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Ce logement n\'est pas disponible.',
            ], 404);
        }

        $property->increment('views_count');
        $property->load(['photos', 'amenities', 'owner', 'reviews.reviewer', 'blockedDates']);

        $blockedDates = $property->blockedDates->map(fn($d) => [
            'start' => $d->start_date->toDateString(),
            'end'   => $d->end_date->toDateString(),
        ]);

        $bookedDates = $property->bookings()
            ->whereIn('status', ['confirmed', 'pending'])
            ->get(['check_in', 'check_out'])
            ->map(fn($b) => [
                'start' => $b->check_in->toDateString(),
                'end'   => $b->check_out->toDateString(),
            ]);

        return response()->json([
            'success' => true,
            'data'    => array_merge(
                $this->formatProperty($property),
                [
                    'amenities'       => $property->amenities->map(fn($a) => [
                        'key'   => $a->amenity,
                        'label' => $a->amenityLabel(),
                    ]),
                    'photos'          => $property->photos->map(fn($p) => [
                        'id'       => $p->id,
                        'url'      => $p->photoUrl(),
                        'caption'  => $p->caption,
                        'is_cover' => $p->is_cover,
                    ]),
                    'reviews'         => $property->reviews->take(5)->map(fn($r) => [
                        'id'             => $r->id,
                        'rating'         => $r->rating_overall,
                        'comment'        => $r->comment,
                        'reviewer_name'  => $r->reviewer->name,
                        'reviewer_avatar'=> $r->reviewer->avatarUrl(),
                        'date'           => $r->created_at->locale('fr')->diffForHumans(),
                    ]),
                    'unavailable_dates' => $blockedDates->merge($bookedDates)->values(),
                    'owner'           => [
                        'id'          => $property->owner->id,
                        'name'        => $property->owner->name,
                        'avatar_url'  => $property->owner->avatarUrl(),
                        'trust_score' => $property->owner->trust_score,
                        'kyc_verified'=> $property->owner->isKycVerified(),
                    ],
                ]
            ),
        ]);
    }

    /**
     * Get featured properties.
     */
    public function featured(Request $request)
    {
        $properties = Property::active()
            ->featured()
            ->with(['photos', 'amenities'])
            ->withCount('reviews')
            ->orderByDesc('rating_avg')
            ->take(10)
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $properties->map(fn($p) => $this->formatProperty($p)),
        ]);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    protected function formatProperty(Property $property): array
    {
        return [
            'id'                => $property->id,
            'title'             => $property->title,
            'type'              => $property->type,
            'type_label'        => $property->typeLabel(),
            'city'              => $property->city,
            'country'           => $property->country,
            'district'          => $property->district,
            'address'           => $property->address,
            'latitude'          => $property->latitude,
            'longitude'         => $property->longitude,
            'capacity'          => $property->capacity,
            'bedrooms'          => $property->bedrooms,
            'bathrooms'         => $property->bathrooms,
            'area_sqm'          => $property->area_sqm,
            'price_per_night'   => $property->price_per_night,
            'price_per_week'    => $property->price_per_week,
            'price_per_month'   => $property->price_per_month,
            'deposit_amount'    => $property->deposit_amount,
            'booking_type'      => $property->booking_type,
            'cancellation_policy' => $property->cancellation_policy,
            'allow_pets'        => $property->allow_pets,
            'allow_smoking'     => $property->allow_smoking,
            'allow_parties'     => $property->allow_parties,
            'check_in_time'     => $property->check_in_time,
            'check_out_time'    => $property->check_out_time,
            'rating_avg'        => $property->rating_avg,
            'rating_count'      => $property->rating_count,
            'reviews_count'     => $property->reviews_count ?? $property->rating_count,
            'views_count'       => $property->views_count,
            'is_featured'       => $property->is_featured,
            'cover_photo_url'   => $property->coverPhotoUrl(),
            'description'       => $property->description,
        ];
    }
}
