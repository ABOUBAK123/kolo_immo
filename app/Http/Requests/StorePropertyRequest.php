<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePropertyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isOwner();
    }

    public function rules(): array
    {
        return [
            // Core fields
            'title'               => ['required', 'string', 'min:5', 'max:150'],
            'description'         => ['required', 'string', 'min:50', 'max:3000'],
            // Accepte les valeurs françaises du formulaire ET les valeurs anglaises
            'type'                => ['required', 'in:studio,apartment,villa,room,duplex,house'],
            'country'             => ['required', 'string', 'max:100'],
            'city'                => ['required', 'string', 'max:100'],
            'district'            => ['nullable', 'string', 'max:100'],
            'address'             => ['nullable', 'string', 'max:255'],
            'latitude'            => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'           => ['nullable', 'numeric', 'between:-180,180'],

            // Capacity
            'capacity'            => ['required', 'integer', 'min:1', 'max:50'],
            'bedrooms'            => ['required', 'integer', 'min:0', 'max:20'],
            'bathrooms'           => ['required', 'integer', 'min:1', 'max:10'],
            'area_sqm'            => ['nullable', 'numeric', 'min:1', 'max:10000'],

            // Pricing
            'price_per_night'     => ['required', 'numeric', 'min:1000'],
            'price_per_week'      => ['nullable', 'numeric', 'min:1000'],
            'price_per_month'     => ['nullable', 'numeric', 'min:1000'],
            'deposit_amount'      => ['nullable', 'numeric', 'min:0'],

            // Booking settings
            'booking_type'        => ['required', 'in:instant,request'],
            'cancellation_policy' => ['required', 'in:flexible,moderate,strict'],
            'check_in_time'       => ['nullable', 'string', 'date_format:H:i'],
            'check_out_time'      => ['nullable', 'string', 'date_format:H:i'],

            // House rules (après prepareForValidation, ce sont allow_*)
            'allow_pets'          => ['sometimes', 'boolean'],
            'allow_smoking'       => ['sometimes', 'boolean'],
            'allow_parties'       => ['sometimes', 'boolean'],

            // Amenities — pas de contrainte stricte sur les valeurs
            'amenities'           => ['nullable', 'array'],
            'amenities.*'         => ['string', 'max:100'],

            // Photos multiples
            'photos'              => ['nullable', 'array', 'max:20'],
            'photos.*'            => ['image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],

            // Cover photo
            'cover_photo'         => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],

            // Status
            'status'              => ['sometimes', 'in:draft,active,inactive'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required'               => 'Le titre du logement est obligatoire.',
            'title.min'                    => 'Le titre doit contenir au moins 5 caractères.',
            'title.max'                    => 'Le titre ne peut pas dépasser 150 caractères.',
            'description.required'         => 'La description est obligatoire.',
            'description.min'              => 'La description doit contenir au moins 50 caractères.',
            'type.required'                => 'Le type de logement est obligatoire.',
            'type.in'                      => 'Type de logement invalide.',
            'country.required'             => 'Le pays est obligatoire.',
            'city.required'                => 'La ville est obligatoire.',
            'capacity.required'            => 'La capacité d\'accueil est obligatoire.',
            'capacity.min'                 => 'La capacité doit être d\'au moins 1 personne.',
            'bedrooms.required'            => 'Le nombre de chambres est obligatoire.',
            'bathrooms.required'           => 'Le nombre de salles de bain est obligatoire.',
            'price_per_night.required'     => 'Le prix par nuit est obligatoire.',
            'price_per_night.min'          => 'Le prix par nuit doit être d\'au moins 1 000 FCFA.',
            'booking_type.required'        => 'Le type de réservation est obligatoire.',
            'cancellation_policy.required' => 'La politique d\'annulation est obligatoire.',
            'check_in_time.date_format'    => 'L\'heure d\'arrivée doit être au format HH:MM.',
            'check_out_time.date_format'   => 'L\'heure de départ doit être au format HH:MM.',
            'cover_photo.image'            => 'La photo de couverture doit être une image.',
            'cover_photo.max'              => 'La photo de couverture ne peut pas dépasser 5 Mo.',
            'photos.*.image'               => 'Chaque fichier doit être une image.',
            'photos.*.max'                 => 'Chaque image ne doit pas dépasser 5 Mo.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $merge = [];

        // Renommage des champs "rules" du formulaire (pets_allowed → allow_pets, etc.)
        if ($this->has('pets_allowed') && !$this->has('allow_pets')) {
            $merge['allow_pets'] = $this->boolean('pets_allowed');
        } else {
            $merge['allow_pets'] = $this->boolean('allow_pets');
        }

        if ($this->has('smoking_allowed') && !$this->has('allow_smoking')) {
            $merge['allow_smoking'] = $this->boolean('smoking_allowed');
        } else {
            $merge['allow_smoking'] = $this->boolean('allow_smoking');
        }

        if ($this->has('parties_allowed') && !$this->has('allow_parties')) {
            $merge['allow_parties'] = $this->boolean('parties_allowed');
        } else {
            $merge['allow_parties'] = $this->boolean('allow_parties');
        }

        // Renommage area → area_sqm si le formulaire envoie "area"
        if ($this->has('area') && !$this->has('area_sqm')) {
            $merge['area_sqm'] = $this->input('area') ?: null;
        }

        // Normalisation des valeurs françaises de type → valeurs ENUM de la DB
        $typeMap = [
            'appartement' => 'apartment',
            'chambre'     => 'room',
            'maison'      => 'house',
        ];
        if ($this->has('type') && isset($typeMap[$this->input('type')])) {
            $merge['type'] = $typeMap[$this->input('type')];
        }

        $this->merge($merge);
    }
}
