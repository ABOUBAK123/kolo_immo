<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePropertyRequest;
use App\Models\Property;
use App\Models\PropertyAmenity;
use App\Models\PropertyBlockedDate;
use App\Models\PropertyPhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PropertyController extends Controller
{
    private function storePhoto($file, string $subdir): string
    {
        $dir = public_path($subdir);
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $file->move($dir, $filename);
        return $subdir . '/' . $filename;
    }

    // ─── PUBLIC ──────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        // Strip malformed date params — redirect to clean URL to avoid WAF 403
        $isDate = fn(?string $v) => $v && preg_match('/^\d{4}-\d{2}-\d{2}$/', $v);
        if (($request->filled('check_in')  && !$isDate($request->check_in)) ||
            ($request->filled('check_out') && !$isDate($request->check_out))) {
            return redirect()->route('properties.index', array_filter(
                $request->except(['check_in', 'check_out']),
                fn($v) => $v !== null && $v !== ''
            ));
        }

        $query = Property::active()
            ->with(['photos', 'amenities', 'owner'])
            ->withCount('reviews');

        if ($request->filled('city')) {
            $query->byCity($request->city);
        }

        if ($request->filled('type')) {
            $query->byType($request->type);
        }

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
            $amenities = (array) $request->amenities;
            foreach ($amenities as $amenity) {
                $query->whereHas('amenities', fn($q) => $q->where('amenity', $amenity));
            }
        }

        $sortBy = $request->get('sort', 'created_at');
        $sortMap = [
            'price_asc'   => ['price_per_night', 'asc'],
            'price_desc'  => ['price_per_night', 'desc'],
            'rating'      => ['rating_avg', 'desc'],
            'newest'      => ['created_at', 'desc'],
        ];

        [$column, $direction] = $sortMap[$sortBy] ?? ['created_at', 'desc'];
        $query->orderBy($column, $direction);

        $properties = $query->paginate(12)->withQueryString();

        $cities    = Property::active()->distinct()->pluck('city')->sort()->values();
        $allLabels = PropertyAmenity::allLabels();

        return view('properties.index', compact('properties', 'cities', 'allLabels'));
    }

    public function show(Property $property)
    {
        if ($property->status !== 'active' && Auth::id() !== $property->owner_id) {
            abort(404);
        }

        $property->increment('views_count');
        $property->load(['photos', 'amenities', 'owner', 'reviews.reviewer', 'blockedDates']);

        $blockedDates = collect($property->blockedDates)->map(fn($d) => [
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

        $unavailableDates = $blockedDates->merge($bookedDates)->values();

        return view('properties.show', compact('property', 'unavailableDates'));
    }

    // ─── OWNER ────────────────────────────────────────────────────────────────

    public function create()
    {
        $allAmenities = PropertyAmenity::allLabels();
        return view('owner.properties.create', compact('allAmenities'));
    }

    public function store(StorePropertyRequest $request)
    {
        $data = $request->validated();
        $data['owner_id'] = Auth::id();
        $data['status']   = 'draft';

        if ($request->hasFile('cover_photo')) {
            $data['cover_photo'] = $request->file('cover_photo')
                ->store('properties/covers', 'public');
        }

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
                    $path = $this->storePhoto($photo, 'properties/photos');
                    $sortOrder++;
                    PropertyPhoto::create([
                        'property_id' => $property->id,
                        'path'        => $path,
                        'sort_order'  => $sortOrder,
                        'is_cover'    => $sortOrder === 1 && !$request->hasFile('cover_photo'),
                    ]);
                }
            }

            return $property;
        });

        return redirect()->route('owner.properties.edit', $property)
            ->with('success', 'Logement créé ! Ajoutez des photos et activez-le pour le publier.');
    }

    public function edit(Property $property)
    {
        if (Auth::id() !== $property->owner_id) abort(403);
        $property->load(['photos', 'amenities']);
        $allAmenities       = PropertyAmenity::allLabels();
        $selectedAmenities  = $property->amenities->pluck('amenity')->toArray();

        return view('owner.properties.edit', compact('property', 'allAmenities', 'selectedAmenities'));
    }

    public function update(StorePropertyRequest $request, Property $property)
    {
        if (Auth::id() !== $property->owner_id) abort(403);

        $data = $request->validated();

        if ($request->hasFile('cover_photo')) {
            if ($property->cover_photo) {
                @unlink(public_path($property->cover_photo));
            }
            $data['cover_photo'] = $this->storePhoto($request->file('cover_photo'), 'properties/covers');
        } else {
            unset($data['cover_photo']);
        }

        $data['check_in_time']  = $data['check_in_time']  ?? $property->check_in_time ?? '14:00';
        $data['check_out_time'] = $data['check_out_time'] ?? $property->check_out_time ?? '11:00';
        $data['deposit_amount'] = $data['deposit_amount'] ?? $property->deposit_amount ?? 0;

        $amenities = array_key_exists('amenities', $data) ? array_unique($data['amenities'] ?? []) : null;

        unset($data['amenities'], $data['photos']);
        $property->update($data);

        if ($amenities !== null) {
            $property->amenities()->delete();
            foreach ($amenities as $amenity) {
                PropertyAmenity::create([
                    'property_id' => $property->id,
                    'amenity'     => $amenity,
                ]);
            }
        }

        if ($request->hasFile('photos')) {
            $sortOrder = $property->photos()->max('sort_order') ?? 0;
            foreach ($request->file('photos') as $photo) {
                $path = $this->storePhoto($photo, 'properties/photos');
                $sortOrder++;
                $isFirst = $sortOrder === 1 && $property->photos()->count() === 1;
                PropertyPhoto::create([
                    'property_id' => $property->id,
                    'path'        => $path,
                    'sort_order'  => $sortOrder,
                    'is_cover'    => $isFirst,
                ]);
            }
        }

        return back()->with('success', 'Logement mis à jour avec succès.');
    }

    public function destroy(Property $property)
    {
        if (Auth::id() !== $property->owner_id) abort(403);
        $property->delete();

        return redirect()->route('owner.properties.index')
            ->with('success', 'Logement supprimé.');
    }

    public function uploadPhotos(Request $request, Property $property)
    {
        if (Auth::id() !== $property->owner_id) abort(403);

        $request->validate([
            'photos'   => ['required', 'array', 'max:20'],
            'photos.*' => ['image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
        ], [
            'photos.required'  => 'Veuillez sélectionner au moins une photo.',
            'photos.*.image'   => 'Le fichier doit être une image.',
            'photos.*.max'     => 'Chaque image ne doit pas dépasser 5 Mo.',
        ]);

        $sortOrder = $property->photos()->max('sort_order') ?? 0;

        foreach ($request->file('photos') as $photo) {
            $path = $this->storePhoto($photo, 'properties/photos');
            $sortOrder++;
            $isFirst = $property->photos()->count() === 0;

            PropertyPhoto::create([
                'property_id' => $property->id,
                'path'        => $path,
                'sort_order'  => $sortOrder,
                'is_cover'    => $isFirst,
            ]);
        }

        return back()->with('success', count($request->file('photos')) . ' photo(s) ajoutée(s).');
    }

    public function deletePhoto(Property $property, PropertyPhoto $photo)
    {
        if (Auth::id() !== $property->owner_id) abort(403);

        if ($photo->property_id !== $property->id) {
            abort(403);
        }

        @unlink(public_path($photo->path));

        if ($photo->is_cover) {
            $next = $property->photos()->where('id', '!=', $photo->id)->first();
            if ($next) {
                $next->update(['is_cover' => true]);
            }
        }

        $photo->delete();

        return back()->with('success', 'Photo supprimée.');
    }

    public function manageAvailability(Property $property)
    {
        if (Auth::id() !== $property->owner_id) abort(403);
        $property->load('blockedDates');

        $blockedDates = $property->blockedDates->map(fn($d) => [
            'id'    => $d->id,
            'start' => $d->start_date->toDateString(),
            'end'   => $d->end_date->toDateString(),
            'reason'=> $d->reason,
            'source'=> $d->source,
        ]);

        return view('owner.properties.availability', compact('property', 'blockedDates'));
    }

    public function saveBlockedDates(Request $request, Property $property)
    {
        if (Auth::id() !== $property->owner_id) abort(403);

        $request->validate([
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date'   => ['required', 'date', 'after:start_date'],
            'reason'     => ['nullable', 'string', 'max:255'],
        ], [
            'start_date.required'      => 'La date de début est obligatoire.',
            'start_date.after_or_equal'=> "La date de début doit être aujourd'hui ou dans le futur.",
            'end_date.required'        => 'La date de fin est obligatoire.',
            'end_date.after'           => 'La date de fin doit être après la date de début.',
        ]);

        PropertyBlockedDate::create([
            'property_id' => $property->id,
            'start_date'  => $request->start_date,
            'end_date'    => $request->end_date,
            'reason'      => $request->reason ?? 'Indisponible',
            'source'      => 'manual',
        ]);

        return back()->with('success', 'Dates bloquées enregistrées.');
    }

    public function deleteBlockedDate(Property $property, PropertyBlockedDate $blockedDate)
    {
        if (Auth::id() !== $property->owner_id) abort(403);

        if ($blockedDate->property_id !== $property->id || $blockedDate->source !== 'manual') {
            abort(403);
        }

        $blockedDate->delete();

        return back()->with('success', 'Dates débloquées.');
    }

    public function ownerIndex()
    {
        $properties = Auth::user()
            ->properties()
            ->withCount(['bookings', 'reviews'])
            ->latest()
            ->paginate(10);

        return view('owner.properties.index', compact('properties'));
    }

    public function toggleStatus(Property $property)
    {
        if (Auth::id() !== $property->owner_id) abort(403);

        // KYC required to publish from the 2nd property onwards
        if ($property->status !== 'active') {
            $user = Auth::user();
            $alreadyPublished = $user->properties()
                ->whereIn('status', ['active', 'inactive'])
                ->where('id', '!=', $property->id)
                ->count();

            if ($alreadyPublished >= 1 && !$user->isKycVerified()) {
                if ($user->kyc_status === 'pending') {
                    return back()->with('warning',
                        'Votre vérification d\'identité est en cours de traitement. Vous pourrez publier ce bien une fois validée par notre équipe.'
                    );
                }
                return back()->with('kyc_required', route('profile.kyc'));
            }
        }

        $newStatus = $property->status === 'active' ? 'inactive' : 'active';
        $property->update(['status' => $newStatus]);

        $label = $newStatus === 'active' ? 'activé' : 'désactivé';
        return back()->with('success', "Logement {$label}.");
    }

    // ─── MAP ─────────────────────────────────────────────────────────────────

    public function mapView()
    {
        $cities = Property::active()->distinct()->pluck('city')->sort()->values();
        return view('properties.map', compact('cities'));
    }

    public function mapData(Request $request)
    {
        $query = Property::active()
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->with(['photos', 'owner'])
            ->withCount('reviews');

        if ($request->filled('city')) {
            $query->byCity($request->city);
        }

        if ($request->filled('type')) {
            $query->byType($request->type);
        }

        if ($request->filled('price_max')) {
            $query->where('price_per_night', '<=', (float) $request->price_max);
        }

        // Radius filter (Haversine)
        if ($request->filled('lat') && $request->filled('lng') && $request->filled('radius')) {
            $lat    = (float) $request->lat;
            $lng    = (float) $request->lng;
            $radius = min((float) $request->radius, 50); // cap 50 km
            $query->withinRadius($lat, $lng, $radius);
        }

        $properties = $query->limit(200)->get();

        $markers = $properties->map(fn($p) => [
            'id'         => $p->id,
            'title'      => $p->title,
            'city'       => $p->city,
            'district'   => $p->district,
            'lat'        => $p->latitude,
            'lng'        => $p->longitude,
            'price'      => $p->price_per_night,
            'type'       => $p->type,
            'rating'     => $p->rating_avg,
            'photo'      => $p->cover_photo_url,
            'url'        => route('properties.show', $p),
            'distance'   => isset($p->distance) ? round($p->distance, 1) : null,
        ]);

        return response()->json(['markers' => $markers]);
    }
}
