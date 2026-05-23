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
    // âââ¬âââ¬âââ¬ PUBLIC âââ¬âââ¬âââ¬âââ¬âââ¬âââ¬âââ¬âââ¬âââ¬âââ¬âââ¬âââ¬âââ¬âââ¬âââ¬âââ¬âââ¬âââ¬âââ¬âââ¬âââ¬âââ¬âââ¬âââ¬âââ¬âââ¬âââ¬âââ¬âââ¬âââ¬âââ¬âââ¬âââ¬âââ¬âââ¬âââ¬âââ¬âââ¬âââ¬âââ¬âââ¬âââ¬âââ¬âââ¬âââ¬âââ¬âââ¬âââ¬âââ¬âââ¬âââ¬âââ¬âââ¬âââ¬âââ¬âââ¬âââ¬âââ¬âââ¬âââ¬âââ¬âââ¬âââ¬

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

    // âââ¬âââ¬âââ¬ OWNER âââ¬âââ¬âââ¬âââ¬âââ¬âââ¬âââ¬âââ¬âââ¬âââ¬âââ¬âââ¬âââ¬âââ¬âââ¬âââ¬âââ¬âââ¬âââ¬âââ¬âââ¬âââ¬âââ¬âââ¬âââ¬âââ¬âââ¬âââ¬âââ¬âââ¬âââ¬âââ¬âââ¬âââ¬âââ¬âââ¬âââ¬âââ¬âââ¬âââ¬âââ¬âââ¬âââ¬âââ¬âââ¬âââ¬âââ¬âââ¬âââ¬âââ¬âââ¬âââ¬âââ¬âââ¬âââ¬âââ¬âââ¬âââ¬âââ¬âââ¬âââ¬âââ¬âââ¬âââ¬

    public function create()
    {
        // Authorization handled by 'owner' middleware on route
        $allAmenities = PropertyAmenity::allLabels();
        return view('owner.properties.create', compact('allAmenities'));
    }

    public function store(StorePropertyRequest $request)
    {
        $data = $request->validated();
        $data['owner_id'] = Auth::id();
        $data['status']   = 'draft';

        // Cover photo unique
        if ($request->hasFile('cover_photo')) {
            $data['cover_photo'] = $request->file('cover_photo')
                ->store('properties/covers', 'public');
        }

        // Valeurs par défaut pour les colonnes NOT NULL qui sont optionnelles dans le formulaire
        $data['check_in_time']  = $data['check_in_time']  ?? '14:00';
        $data['check_out_time'] = $data['check_out_time'] ?? '11:00';
        $data['deposit_amount'] = $data['deposit_amount'] ?? 0;

        // Retirer amenities et photos du tableau avant création
        $amenities = $data['amenities'] ?? [];
        unset($data['amenities'], $data['photos']);

        $property = Property::create($data);

        // Ãquipements
        foreach ($amenities as $amenity) {
            PropertyAmenity::create([
                'property_id' => $property->id,
                'amenity'     => $amenity,
            ]);
        }

        // Photos multiples (champ photos[])
        if ($request->hasFile('photos')) {
            $sortOrder = 0;
            foreach ($request->file('photos') as $photo) {
                $path = $photo->store('properties/photos', 'public');
                $sortOrder++;
                PropertyPhoto::create([
                    'property_id' => $property->id,
                    'path'        => $path,
                    'sort_order'  => $sortOrder,
                    'is_cover'    => $sortOrder === 1 && !$request->hasFile('cover_photo'),
                ]);
            }
        }

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
                Storage::disk('public')->delete($property->cover_photo);
            }
            $data['cover_photo'] = $request->file('cover_photo')
                ->store('properties/covers', 'public');
        } else {
            // Ne pas écraser la cover_photo existante si aucun nouveau fichier
            unset($data['cover_photo']);
        }

        // Valeurs par défaut pour les colonnes NOT NULL optionnelles
        $data['check_in_time']  = $data['check_in_time']  ?? $property->check_in_time ?? '14:00';
        $data['check_out_time'] = $data['check_out_time'] ?? $property->check_out_time ?? '11:00';
        $data['deposit_amount'] = $data['deposit_amount'] ?? $property->deposit_amount ?? 0;

        // Save before unsetting
        $amenities = array_key_exists('amenities', $data) ? ($data['amenities'] ?? []) : null;

        unset($data['amenities'], $data['photos']);
        $property->update($data);

        // Update amenities when form sends the field
        if ($amenities !== null) {
            $property->amenities()->delete();
            foreach ((array) $amenities as $amenity) {
                PropertyAmenity::create([
                    'property_id' => $property->id,
                    'amenity'     => $amenity,
                ]);
            }
        }

        // Handle new photos uploaded via the edit form
        if ($request->hasFile('photos')) {
            $sortOrder = $property->photos()->max('sort_order') ?? 0;
            foreach ($request->file('photos') as $photo) {
                $path = $photo->store('properties/photos', 'public');
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
            ->with('success', 'Logement supprimÃÂ©.');
    }

    public function uploadPhotos(Request $request, Property $property)
    {
        if (Auth::id() !== $property->owner_id) abort(403);

        $request->validate([
            'photos'   => ['required', 'array', 'max:20'],
            'photos.*' => ['image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
        ], [
            'photos.required'  => 'Veuillez sÃÂ©lectionner au moins une photo.',
            'photos.*.image'   => 'Le fichier doit ÃÂªtre une image.',
            'photos.*.max'     => 'Chaque image ne doit pas dÃÂ©passer 5 Mo.',
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

        return back()->with('success', count($request->file('photos')) . ' photo(s) ajoutÃÂ©e(s).');
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

        return back()->with('success', 'Photo supprimÃÂ©e.');
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
            'start_date.required'      => 'La date de dÃÂ©but est obligatoire.',
            'start_date.after_or_equal'=> 'La date de dÃÂ©but doit ÃÂªtre aujourd\'hui ou dans le futur.',
            'end_date.required'        => 'La date de fin est obligatoire.',
            'end_date.after'           => 'La date de fin doit ÃÂªtre aprÃÂ¨s la date de dÃÂ©but.',
        ]);

        PropertyBlockedDate::create([
            'property_id' => $property->id,
            'start_date'  => $request->start_date,
            'end_date'    => $request->end_date,
            'reason'      => $request->reason ?? 'Indisponible',
            'source'      => 'manual',
        ]);

        return back()->with('success', 'Dates bloquÃÂ©es enregistrÃÂ©es.');
    }

    public function deleteBlockedDate(Property $property, PropertyBlockedDate $blockedDate)
    {
        if (Auth::id() !== $property->owner_id) abort(403);

        if ($blockedDate->property_id !== $property->id || $blockedDate->source !== 'manual') {
            abort(403);
        }

        $blockedDate->delete();

        return back()->with('success', 'Dates dÃÂ©bloquÃÂ©es.');
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

        $label = $newStatus === 'active' ? 'activÃÂ©' : 'dÃÂ©sactivÃÂ©';
        return back()->with('success', "Logement {$label}.");
    }
}

