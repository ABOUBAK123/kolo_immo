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
            'wifi'             => 'Wi-Fi',
            'air_conditioning' => 'Climatisation',
            'equipped_kitchen' => 'Cuisine équipée',
            'parking'          => 'Parking',
            'generator'        => 'Groupe électrogène',
            'pool'             => 'Piscine',
            'gym'              => 'Salle de sport',
            'security'         => 'Sécurité 24h',
            'tv'               => 'Télévision',
            'washer'           => 'Machine à laver',
            'dryer'            => 'Sèche-linge',
            'iron'             => 'Fer à repasser',
            'workspace'        => 'Espace de travail',
            'balcony'          => 'Balcon',
            'garden'           => 'Jardin',
            'elevator'         => 'Ascenseur',
            'hot_water'        => 'Eau chaude',
            'solar_power'      => 'Énergie solaire',
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
            'air_conditioning' => '❄️',
            'equipped_kitchen' => '🍳',
            'parking'          => '🅿️',
            'generator'        => '⚡',
            'pool'             => '🏊',
            'gym'              => '💪',
            'security'         => '🔒',
            'tv'               => '📺',
            'washer'           => '🫧',
            'dryer'            => '🌀',
            'iron'             => '👔',
            'workspace'        => '💼',
            'balcony'          => '🏠',
            'garden'           => '🌿',
            'elevator'         => '🛗',
            'hot_water'        => '🚿',
            'solar_power'      => '☀️',
            default            => '✓',
        };
    }
}
