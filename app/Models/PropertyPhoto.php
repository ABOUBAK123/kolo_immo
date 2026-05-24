<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PropertyPhoto extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_id', 'path', 'caption', 'sort_order', 'is_cover',
    ];

    protected function casts(): array
    {
        return [
            'is_cover'   => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    // ─── Relationships ────────────────────────────────────────────────────────

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    public function photoUrl(): string
    {
        if ($this->path) {
            // If path looks like a full URL (placeholder), return as-is
            if (str_starts_with($this->path, 'http')) {
                return $this->path;
            }
            return asset($this->path);
        }

        return asset('images/property-placeholder.jpg');
    }
}
