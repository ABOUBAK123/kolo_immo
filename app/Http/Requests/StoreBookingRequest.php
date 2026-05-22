<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'property_id'      => ['required', 'exists:properties,id'],
            'check_in'         => ['required', 'date', 'after_or_equal:today'],
            'check_out'        => ['required', 'date', 'after:check_in'],
            'guests'           => ['required', 'integer', 'min:1', 'max:50'],
            'special_requests' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Get custom error messages.
     */
    public function messages(): array
    {
        return [
            'property_id.required'      => 'Le logement est obligatoire.',
            'property_id.exists'        => 'Ce logement n\'existe pas.',
            'check_in.required'         => 'La date d\'arrivée est obligatoire.',
            'check_in.date'             => 'La date d\'arrivée n\'est pas valide.',
            'check_in.after_or_equal'   => 'La date d\'arrivée doit être aujourd\'hui ou dans le futur.',
            'check_out.required'        => 'La date de départ est obligatoire.',
            'check_out.date'            => 'La date de départ n\'est pas valide.',
            'check_out.after'           => 'La date de départ doit être après la date d\'arrivée.',
            'guests.required'           => 'Le nombre de voyageurs est obligatoire.',
            'guests.integer'            => 'Le nombre de voyageurs doit être un entier.',
            'guests.min'                => 'Il faut au moins 1 voyageur.',
            'guests.max'                => 'Le nombre maximum de voyageurs est de 50.',
            'special_requests.max'      => 'Les demandes spéciales ne peuvent pas dépasser 500 caractères.',
        ];
    }
}
