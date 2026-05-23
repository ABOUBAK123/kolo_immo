<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PropertyAmenity extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_id', 'amenity',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    public static function allLabels(): array
    {
        return [
            // Internet & Tech
            'wifi'             => 'Wi-Fi',
            'tv'               => 'Télévision',
            'cable_tv'         => 'TV câble/satellite',
            // Confort
            'air_conditioning' => 'Climatisation',
            'fan'              => 'Ventilateur',
            'heating'          => 'Chauffage',
            // Cuisine
            'equipped_kitchen' => 'Cuisine équipée',
            'refrigerator'     => 'Réfrigérateur',
            'oven'             => 'Four/Micro-ondes',
            'coffee_machine'   => 'Machine à café',
            // Sécurité
            'security'         => 'Sécurité 24h',
            'cctv'             => 'Caméras de sécurité',
            'intercom'         => 'Interphone',
            'safe'             => 'Coffre-fort',
            // Extérieur
            'pool'             => 'Piscine',
            'parking'          => 'Parking',
            'balcony'          => 'Balcon/Terrasse',
            'garden'           => 'Jardin',
            // Services
            'elevator'         => 'Ascenseur',
            'washer'           => 'Machine à laver',
            'dryer'            => 'Sèche-linge',
            'iron'             => 'Fer à repasser',
            'workspace'        => 'Espace de travail',
            'hot_water'        => 'Eau chaude',
            'generator'        => 'Groupe électrogène',
            'solar_power'      => 'Énergie solaire',
            'gym'              => 'Salle de sport',
        ];
    }

    public function amenityLabel(): string
    {
        return static::allLabels()[$this->amenity] ?? ucfirst(str_replace('_', ' ', $this->amenity));
    }

    public function amenityIcon(): string
    {
        return match($this->amenity) {
            'wifi'             => '📶',
            'tv'               => '📺',
            'cable_tv'         => '📡',
            'air_conditioning' => '❄️',
            'fan'              => '🌬️',
            'heating'          => '🔥',
            'equipped_kitchen' => '🍳',
            'refrigerator'     => '🧊',
            'oven'             => '♨️',
            'coffee_machine'   => '☕',
            'security'         => '🔒',
            'cctv'             => '📹',
            'intercom'         => '🔔',
            'safe'             => '🗄️',
            'pool'             => '🏊',
            'parking'          => '🅿️',
            'balcony'          => '🏠',
            'garden'           => '🌿',
            'elevator'         => '🛗',
            'washer'           => '🫧',
            'dryer'            => '🌀',
            'iron'             => '👔',
            'workspace'        => '💼',
            'hot_water'        => '🚿',
            'generator'        => '⚡',
            'solar_power'      => '☀️',
            'gym'              => '💪',
            default            => '✓',
        };
    }
}
