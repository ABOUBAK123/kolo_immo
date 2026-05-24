<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\PropertyAmenity;
use App\Models\PropertyPhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OwnerPropertyController extends Controller
{
    private function storePhoto($file, string $subdir = 'properties/photos'): string
    {
        $dir = public_path($subdir);
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $file->move($dir, $filename);
        return $subdir . '/' . $filename;
    }

    // ─── LIST OWNER'S PROPERTIES ─────────────────────────────────────────────

    public function index(Request $request)
    {
        $properties = Property::where('owner_id', $request->user()->id)
            ->with(['photos'])
            ->withCount(['bookings', 'reviews'])
            ->orderByDesc('created_at')
            ->paginate(20);

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

    // ─── CREATE PROPERTY ──────────────────────────────────────────────────────

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'               => ['required', 'string', 'min:5', 'max:150'],
            'description'         => ['required', 'string', 'min:50', 'max:3000'],
            'type'                => ['required', 'in:studio,apartment,villa,room,duplex,house'],
            'country'             => ['required', 'string', 'max:100'],
            'city'                => ['required', 'string', 'max:100'],
            'district'            => ['nullable', 'string', 'max:100'],
            'address'             => ['nullable', 'string', 'max:255'],
            'latitude'            => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'           => ['nullable', 'numeric', 'between:-180,180'],
            'capacity'            => ['required', 'integer', 'min:1', 'max:50'],
            'bedrooms'            => ['required', 'integer', 'min:0', 'max:20'],
            'bathrooms'           => ['required', 'integer', 'min:1', 'max:10'],
            'area_sqm'            => ['nullable', 'numeric', 'min:1'],
            'price_per_night'     => ['required', 'numeric', 'min:1000'],
            'price_per_week'      => ['nullable', 'numeric', 'min:1000'],
            'price_per_month'     => ['nullable', 'numeric', 'min:1000'],
            'deposit_amount'      => ['nullable', 'numeric', 'min:0'],
            'booking_type'        => ['required', 'in:instant,request'],
            'cancellation_policy' => ['required', 'in:flexible,moderate,strict'],
            'check_in_time'       => ['nullable', 'string', 'date_format:H:i'],
            'check_out_time'      => ['nullable', 'string', 'date_format:H:i'],
            'allow_pets'          => ['sometimes', 'boolean'],
            'allow_smoking'       => ['sometimes', 'boolean'],
            'allow_parties'       => ['sometimes', 'boolean'],
            'additional_rules'    => ['nullable', 'string', 'max:1000'],
            'amenities'           => ['nullable', 'array'],
            'amenities.*'         => ['string', 'max:100'],
            'photos'              => ['nullable', 'array', 'max:20'],
            'photos.*'            => ['image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
        ]);

        $data['owner_id']       = $request->user()->id;
        $data['status']         = 'draft';
        $data['check_in_time']  = $data['check_in_time']  ?? '14:00';
        $data['check_out_time'] = $data['check_out_time'] ?? '11:00';
        $data['deposit_amount'] = $data['deposit_amount'] ?? 0;

        $amenities = array_unique($data['amenities'] ?? []);
        unset($data['amenities'], $data['photos']);

        $property = DB::transaction(function () use ($data, $amenities, $request) {
            $property = Property::create($data);

            foreach ($amenities as $amenity) {
                PropertyAmenity::firstOrCreate([
                    'property_id' => $property->id,
                    'amenity'     => $amenity,
                ]);
            }

            if ($request->hasFile('photos')) {
                $sortOrder = 0;
                foreach ($request->file('photos') as $photo) {
                    $path = $this->storePhoto($photo);
                    $sortOrder++;
                    PropertyPhoto::create([
                        'property_id' => $property->id,
                        'path'        => $path,
                        'sort_order'  => $sortOrder,
                        'is_cover'    => $sortOrder === 1,
                    ]);
                }
            }

            return $property;
        });

        $property->load(['photos', 'amenities']);

        return response()->json([
            'success' => true,
            'message' => 'Logement créé avec succès.',
            'data'    => $this->formatProperty($property),
        ], 201);
    }

    // ─── SHOW ONE PROPERTY ────────────────────────────────────────────────────

    public function show(Request $request, Property $property)
    {
        if ($property->owner_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Accès refusé.'], 403);
        }

        $property->load(['photos', 'amenities']);

        return response()->json([
            'success' => true,
            'data'    => array_merge($this->formatProperty($property), [
                'amenities' => $property->amenities->map(fn($a) => [
                    'key'   => $a->amenity,
                    'label' => $a->amenityLabel(),
                ]),
                'photos' => $property->photos->map(fn($p) => [
                    'id'       => $p->id,
                    'url'      => $p->photoUrl(),
                    'is_cover' => $p->is_cover,
                    'caption'  => $p->caption,
                ]),
            ]),
        ]);
    }

    // ─── UPDATE PROPERTY ──────────────────────────────────────────────────────

    public function update(Request $request, Property $property)
    {
        if ($property->owner_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Accès refusé.'], 403);
        }

        $data = $request->validate([
            'title'               => ['sometimes', 'string', 'min:5', 'max:150'],
            'description'         => ['sometimes', 'string', 'min:50', 'max:3000'],
            'type'                => ['sometimes', 'in:studio,apartment,villa,room,duplex,house'],
            'country'             => ['sometimes', 'string', 'max:100'],
            'city'                => ['sometimes', 'string', 'max:100'],
            'district'            => ['nullable', 'string', 'max:100'],
            'address'             => ['nullable', 'string', 'max:255'],
            'latitude'            => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'           => ['nullable', 'numeric', 'between:-180,180'],
            'capacity'            => ['sometimes', 'integer', 'min:1', 'max:50'],
            'bedrooms'            => ['sometimes', 'integer', 'min:0', 'max:20'],
            'bathrooms'           => ['sometimes', 'integer', 'min:1', 'max:10'],
            'area_sqm'            => ['nullable', 'numeric', 'min:1'],
            'price_per_night'     => ['sometimes', 'numeric', 'min:1000'],
            'price_per_week'      => ['nullable', 'numeric', 'min:1000'],
            'price_per_month'     => ['nullable', 'numeric', 'min:1000'],
            'deposit_amount'      => ['nullable', 'numeric', 'min:0'],
            'booking_type'        => ['sometimes', 'in:instant,request'],
            'cancellation_policy' => ['sometimes', 'in:flexible,moderate,strict'],
            'check_in_time'       => ['nullable', 'string', 'date_format:H:i'],
            'check_out_time'      => ['nullable', 'string', 'date_format:H:i'],
            'allow_pets'          => ['sometimes', 'boolean'],
            'allow_smoking'       => ['sometimes', 'boolean'],
            'allow_parties'       => ['sometimes', 'boolean'],
            'additional_rules'    => ['nullable', 'string', 'max:1000'],
            'amenities'           => ['nullable', 'array'],
            'amenities.*'         => ['string', 'max:100'],
            'photos'              => ['nullable', 'array', 'max:20'],
            'photos.*'            => ['image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
        ]);

        $amenities = array_key_exists('amenities', $data) ? array_unique($data['amenities'] ?? []) : null;
        unset($data['amenities'], $data['photos']);

        $property->update($data);

        if ($amenities !== null) {
            $property->amenities()->delete();
            foreach ($amenities as $amenity) {
                PropertyAmenity::firstOrCreate([
                    'property_id' => $property->id,
                    'amenity'     => $amenity,
                ]);
            }
        }

        if ($request->hasFile('photos')) {
            $sortOrder = $property->photos()->max('sort_order') ?? 0;
            foreach ($request->file('photos') as $photo) {
                $path = $this->storePhoto($photo);
                $sortOrder++;
                PropertyPhoto::create([
                    'property_id' => $property->id,
                    'path'        => $path,
                    'sort_order'  => $sortOrder,
                    'is_cover'    => false,
                ]);
            }
        }

        $property->load(['photos', 'amenities']);

        return response()->json([
            'success' => true,
            'message' => 'Logement mis à jour.',
            'data'    => $this->formatProperty($property),
        ]);
    }

    // ─── DELETE PROPERTY ──────────────────────────────────────────────────────

    public function destroy(Request $request, Property $property)
    {
        if ($property->owner_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Accès refusé.'], 403);
        }

        $property->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logement supprimé.',
        ]);
    }

    // ─── TOGGLE STATUS (ACTIVATE / DEACTIVATE) ───────────────────────────────

    public function toggleStatus(Request $request, Property $property)
    {
        if ($property->owner_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Accès refusé.'], 403);
        }

        if ($property->status === 'suspended') {
            return response()->json([
                'success' => false,
                'message' => 'Ce logement est suspendu par l\'administrateur.',
            ], 403);
        }

        if ($property->status !== 'active') {
            $user = $request->user();
            $alreadyPublished = $user->properties()
                ->whereIn('status', ['active', 'inactive'])
                ->where('id', '!=', $property->id)
                ->count();

            if ($alreadyPublished >= 1 && !$user->isKycVerified()) {
                $message = $user->kyc_status === 'pending'
                    ? 'Votre vérification d\'identité est en cours de traitement.'
                    : 'Vous devez valider votre identité (KYC) pour publier plusieurs biens.';

                return response()->json([
                    'success'    => false,
                    'kyc_required' => true,
                    'kyc_status' => $user->kyc_status,
                    'message'    => $message,
                ], 403);
            }
        }

        $newStatus = $property->status === 'active' ? 'inactive' : 'active';
        $property->update(['status' => $newStatus]);

        return response()->json([
            'success' => true,
            'message' => $newStatus === 'active' ? 'Logement activé.' : 'Logement désactivé.',
            'data'    => ['status' => $newStatus],
        ]);
    }

    // ─── UPLOAD PHOTOS ────────────────────────────────────────────────────────

    public function uploadPhotos(Request $request, Property $property)
    {
        if ($property->owner_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Accès refusé.'], 403);
        }

        $request->validate([
            'photos'   => ['required', 'array', 'max:20'],
            'photos.*' => ['image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
        ]);

        $sortOrder = $property->photos()->max('sort_order') ?? 0;
        $added = [];

        foreach ($request->file('photos') as $photo) {
            $path = $this->storePhoto($photo);
            $sortOrder++;
            $isFirst = $sortOrder === 1 && $property->photos()->count() === 0;

            $record = PropertyPhoto::create([
                'property_id' => $property->id,
                'path'        => $path,
                'sort_order'  => $sortOrder,
                'is_cover'    => $isFirst,
            ]);

            $added[] = [
                'id'       => $record->id,
                'url'      => $record->photoUrl(),
                'is_cover' => $record->is_cover,
            ];
        }

        return response()->json([
            'success' => true,
            'message' => count($added) . ' photo(s) ajoutée(s).',
            'data'    => ['photos' => $added],
        ]);
    }

    // ─── DELETE PHOTO ─────────────────────────────────────────────────────────

    public function deletePhoto(Request $request, Property $property, PropertyPhoto $photo)
    {
        if ($property->owner_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Accès refusé.'], 403);
        }

        if ($photo->property_id !== $property->id) {
            return response()->json(['success' => false, 'message' => 'Photo introuvable.'], 404);
        }

        @unlink(public_path($photo->path));

        if ($photo->is_cover) {
            $next = $property->photos()->where('id', '!=', $photo->id)->first();
            if ($next) {
                $next->update(['is_cover' => true]);
            }
        }

        $photo->delete();

        return response()->json([
            'success' => true,
            'message' => 'Photo supprimée.',
        ]);
    }

    // ─── HELPERS ──────────────────────────────────────────────────────────────

    protected function formatProperty(Property $property): array
    {
        return [
            'id'                  => $property->id,
            'title'               => $property->title,
            'type'                => $property->type,
            'status'              => $property->status,
            'city'                => $property->city,
            'country'             => $property->country,
            'district'            => $property->district,
            'address'             => $property->address,
            'latitude'            => $property->latitude,
            'longitude'           => $property->longitude,
            'capacity'            => $property->capacity,
            'bedrooms'            => $property->bedrooms,
            'bathrooms'           => $property->bathrooms,
            'area_sqm'            => $property->area_sqm,
            'price_per_night'     => $property->price_per_night,
            'price_per_week'      => $property->price_per_week,
            'price_per_month'     => $property->price_per_month,
            'deposit_amount'      => $property->deposit_amount,
            'booking_type'        => $property->booking_type,
            'cancellation_policy' => $property->cancellation_policy,
            'allow_pets'          => $property->allow_pets,
            'allow_smoking'       => $property->allow_smoking,
            'allow_parties'       => $property->allow_parties,
            'check_in_time'       => $property->check_in_time,
            'check_out_time'      => $property->check_out_time,
            'rating_avg'          => $property->rating_avg,
            'rating_count'        => $property->rating_count,
            'views_count'         => $property->views_count,
            'is_featured'         => $property->is_featured,
            'cover_photo_url'     => $property->coverPhotoUrl(),
            'description'         => $property->description,
            'bookings_count'      => $property->bookings_count ?? null,
            'reviews_count'       => $property->reviews_count ?? null,
            'created_at'          => $property->created_at?->toDateTimeString(),
        ];
    }
}
