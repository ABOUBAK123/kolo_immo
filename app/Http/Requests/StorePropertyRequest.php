<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePropertyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Authorization is handled in the controller via policies
        return auth()->check() && auth()->user()->isOwner();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            // Core fields
            'title'               => ['required', 'string', 'min:5', 'max:150'],
            'description'         => ['required', 'string', 'min:50', 'max:3000'],
            'type'                => ['required', 'in:studio,apartment,villa,room,duplex'],
            'country'             => ['required', 'string', 'max:100'],
            'city'                => ['required', 'string', 'max:100'],
            'district'            => ['nullable', 'string', 'max:100'],
            'address'             => ['required', 'string', 'max:255'],
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
            'deposit_amount'      => ['required', 'numeric', 'min:0'],

            // Booking settings
            'booking_type'        => ['required', 'in:instant,request'],
            'cancellation_policy' => ['required', 'in:flexible,moderate,strict'],
            'check_in_time'       => ['required', 'string', 'date_format:H:i'],
            'check_out_time'      => ['required', 'string', 'date_format:H:i'],

            // House rules
            'allow_pets'          => ['sometimes', 'boolean'],
            'allow_smoking'       => ['sometimes', 'boolean'],
            'allow_parties'       => ['sometimes', 'boolean'],

            // Amenities
            'amenities'           => ['nullable', 'array'],
            'amenities.*'         => ['string', 'in:wifi,air_conditioning,equipped_kitchen,parking,generator,pool,gym,security,tv,washer,dryer,iron,workspace,balcony,garden,elevator,hot_water,solar_power'],

            // Cover photo (optional on update)
            'cover_photo'         => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],

            // Status
            'status'              => ['sometimes', 'in:draft,active,inactive'],
        ];
    }

    /**
     * Get custom error messages.
     */
    public function messages(): array
    {
        return [
            'title.required'           => 'Le titre du logement est obligatoire.',
            'title.min'                => 'Le titre doit contenir au moins 5 caractères.',
            'title.max'                => 'Le titre ne peut pas dépasser 150 caractères.',
            'description.required'     => 'La description est obligatoire.',
            'description.min'          => 'La description doit contenir au moins 50 caractères.',
            'type.required'            => 'Le type de logement est obligatoire.',
            'type.in'                  => 'Type de logement invalide. Choisissez parmi : Studio, Appartement, Villa, Chambre, Duplex.',
            'country.required'         => 'Le pays est obligatoire.',
            'city.required'            => 'La ville est obligatoire.',
            'address.required'         => 'L\'adresse est obligatoire.',
            'capacity.required'        => 'La capacité d\'accueil est obligatoire.',
            'capacity.min'             => 'La capacité doit être d\'au moins 1 personne.',
            'bedrooms.required'        => 'Le nombre de chambres est obligatoire.',
            'bathrooms.required'       => 'Le nombre de salles de bain est obligatoire.',
            'price_per_night.required' => 'Le prix par nuit est obligatoire.',
            'price_per_night.min'      => 'Le prix par nuit doit être d\'au moins 1 000 FCFA.',
            'deposit_amount.required'  => 'Le montant de la caution est obligatoire.',
            'booking_type.required'    => 'Le type de réservation est obligatoire.',
            'booking_type.in'          => 'Type de réservation invalide.',
            'cancellation_policy.required' => 'La politique d\'annulation est obligatoire.',
            'check_in_time.required'   => 'L\'heure d\'arrivée est obligatoire.',
            'check_out_time.required'  => 'L\'heure de départ est obligatoire.',
            'check_in_time.date_format'  => 'L\'heure d\'arrivée doit être au format HH:MM.',
            'check_out_time.date_format' => 'L\'heure de départ doit être au format HH:MM.',
            'cover_photo.image'        => 'La photo de couverture doit être une image.',
            'cover_photo.max'          => 'La photo de couverture ne peut pas dépasser 5 Mo.',
            'amenities.*.in'           => 'Un ou plusieurs équipements sélectionnés sont invalides.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Convert checkbox values to booleans
        $this->merge([
            'allow_pets'     => $this->boolean('allow_pets'),
            'allow_smoking'  => $this->boolean('allow_smoking'),
            'allow_parties'  => $this->boolean('allow_parties'),
        ]);
    }
}
