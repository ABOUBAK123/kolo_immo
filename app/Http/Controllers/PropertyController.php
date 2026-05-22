<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePropertyRequest;
use App\Models\Property;
use App\Models\PropertyAmenity;
use App\Models\PropertyBlockedDate;
use App\Models\PropertyPhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PropertyController extends Controller
{
    // â”€â”€â”€ PUBLIC â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    /**
     * Search and list properties.
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

        if ($request->filled('check_in') && $request->filled('check_out')) {
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

    /**
     * Show a single property.
     */
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

    // â”€â”€â”€ OWNER â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    public function create()
    {
        // Authorization handled by 'owner' middleware on route
        $allAmenities = PropertyAmenity::allLabels();
        return view('owner.properties.create', compact('allAmenities'));
    }

    public function store(StorePropertyRequest $request)
    {
        // Authorization handled by 'owner' middleware on route

        $data = $request->validated();
        $data['owner_id'] = Auth::id();
        $data['status']   = 'draft';

        // Handle cover photo
        if ($request->hasFile('cover_photo')) {
            $data['cover_photo'] = $request->file('cover_photo')
                ->store('properties/covers', 'public');
        }

        $property = Property::create($data);

        // Store amenities
        if (!empty($data['amenities'])) {
            foreach ($data['amenities'] as $amenity) {
                PropertyAmenity::create([
                    'property_id' => $property->id,
                    'amenity'     => $amenity,
                ]);
            }
        }

        return redirect()->route('owner.properties.edit', $property)
            ->with('success', 'Logement crÃ©Ã© ! Ajoutez maintenant des photos et activez-le.');
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
            // Delete old cover
            if ($property->cover_photo) {
                Storage::disk('public')->delete($property->cover_photo);
            }
            $data['cover_photo'] = $request->file('cover_photo')
                ->store('properties/covers', 'public');
        }

        $property->update($data);

        // Update amenities
        if (array_key_exists('amenities', $data)) {
            $property->amenities()->delete();
            foreach ((array) ($data['amenities'] ?? []) as $amenity) {
                PropertyAmenity::create([
                    'property_id' => $property->id,
                    'amenity'     => $amenity,
                ]);
            }
        }

        return back()->with('success', 'Logement mis Ã  jour avec succÃ¨s.');
    }

    public function destroy(Property $property)
    {
        if (Auth::id() !== $property->owner_id) abort(403);
        $property->delete();

        return redirect()->route('owner.properties.index')
            ->with('success', 'Logement supprimÃ©.');
    }

    public function uploadPhotos(Request $request, Property $property)
    {
        if (Auth::id() !== $property->owner_id) abort(403);

        $request->validate([
            'photos'   => ['required', 'array', 'max:20'],
            'photos.*' => ['image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
        ], [
            'photos.required'  => 'Veuillez sÃ©lectionner au moins une photo.',
            'photos.*.image'   => 'Le fichier doit Ãªtre une image.',
            'photos.*.max'     => 'Chaque image ne doit pas dÃ©passer 5 Mo.',
        ]);

        $sortOrder = $property->photos()->max('sort_order') ?? 0;

        foreach ($request->file('photos') as $photo) {
            $path = $photo->store('properties/photos', 'public');
            $sortOrder++;
            $isFirst = $property->photos()->count() === 0;

            PropertyPhoto::create([
                'property_id' => $property->id,
                'path'        => $path,
                'sort_order'  => $sortOrder,
                'is_cover'    => $isFirst,
            ]);
        }

        return back()->with('success', count($request->file('photos')) . ' photo(s) ajoutÃ©e(s).');
    }

    public function deletePhoto(Property $property, PropertyPhoto $photo)
    {
        if (Auth::id() !== $property->owner_id) abort(403);

        if ($photo->property_id !== $property->id) {
            abort(403);
        }

        Storage::disk('public')->delete($photo->path);

        // If it was the cover, assign cover to next photo
        if ($photo->is_cover) {
            $next = $property->photos()->where('id', '!=', $photo->id)->first();
            if ($next) {
                $next->update(['is_cover' => true]);
            }
        }

        $photo->delete();

        return back()->with('success', 'Photo supprimÃ©e.');
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
            'start_date.required'      => 'La date de dÃ©but est obligatoire.',
            'start_date.after_or_equal'=> 'La date de dÃ©but doit Ãªtre aujourd\'hui ou dans le futur.',
            'end_date.required'        => 'La date de fin est obligatoire.',
            'end_date.after'           => 'La date de fin doit Ãªtre aprÃ¨s la date de dÃ©but.',
        ]);

        PropertyBlockedDate::create([
            'property_id' => $property->id,
            'start_date'  => $request->start_date,
            'end_date'    => $request->end_date,
            'reason'      => $request->reason ?? 'Indisponible',
            'source'      => 'manual',
        ]);

        return back()->with('success', 'Dates bloquÃ©es enregistrÃ©es.');
    }

    public function deleteBlockedDate(Property $property, PropertyBlockedDate $blockedDate)
    {
        if (Auth::id() !== $property->owner_id) abort(403);

        if ($blockedDate->property_id !== $property->id || $blockedDate->source !== 'manual') {
            abort(403);
        }

        $blockedDate->delete();

        return back()->with('success', 'Dates dÃ©bloquÃ©es.');
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

        $newStatus = $property->status === 'active' ? 'inactive' : 'active';
        $property->update(['status' => $newStatus]);

        $label = $newStatus === 'active' ? 'activÃ©' : 'dÃ©sactivÃ©';
        return back()->with('success', "Logement {$label}.");
    }
}

