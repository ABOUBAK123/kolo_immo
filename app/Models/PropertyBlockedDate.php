<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PropertyBlockedDate extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_id', 'start_date', 'end_date', 'reason', 'source',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date'   => 'date',
        ];
    }

    // ─── Relationships ────────────────────────────────────────────────────────

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    public function sourceLabel(): string
    {
        return match($this->source) {
            'manual'  => 'Bloqué manuellement',
            'booking' => 'Réservation',
            default   => ucfirst($this->source),
        };
    }
}
