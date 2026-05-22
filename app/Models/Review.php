<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id', 'reviewer_id', 'reviewee_id', 'property_id', 'type',
        'rating_overall', 'rating_cleanliness', 'rating_communication',
        'rating_accuracy', 'rating_location', 'rating_value',
        'comment', 'owner_reply', 'owner_replied_at',
        'is_flagged', 'flag_reason',
    ];

    protected function casts(): array
    {
        return [
            'rating_overall'       => 'float',
            'rating_cleanliness'   => 'float',
            'rating_communication' => 'float',
            'rating_accuracy'      => 'float',
            'rating_location'      => 'float',
            'rating_value'         => 'float',
            'owner_replied_at'     => 'datetime',
            'is_flagged'           => 'boolean',
        ];
    }

    // ─── Relationships ────────────────────────────────────────────────────────

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function reviewee()
    {
        return $this->belongsTo(User::class, 'reviewee_id');
    }

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeForProperty($query, int $propertyId)
    {
        return $query->where('property_id', $propertyId)
                     ->where('type', 'tenant_to_property');
    }

    public function scopeForTenant($query, int $userId)
    {
        return $query->where('reviewee_id', $userId)
                     ->where('type', 'owner_to_tenant');
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    public function averageRating(): float
    {
        $ratings = array_filter([
            $this->rating_cleanliness,
            $this->rating_communication,
            $this->rating_accuracy,
            $this->rating_location,
            $this->rating_value,
        ]);

        if (empty($ratings)) {
            return $this->rating_overall ?? 0;
        }

        return round(array_sum($ratings) / count($ratings), 1);
    }

    public function starDisplay(float $rating): string
    {
        $full  = (int) $rating;
        $half  = ($rating - $full) >= 0.5 ? 1 : 0;
        $empty = 5 - $full - $half;

        return str_repeat('★', $full) . str_repeat('½', $half) . str_repeat('☆', $empty);
    }
}
