<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Property extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'owner_id', 'title', 'description', 'type', 'country', 'city',
        'district', 'address', 'latitude', 'longitude', 'capacity',
        'bedrooms', 'bathrooms', 'area_sqm', 'price_per_night',
        'price_per_week', 'price_per_month', 'deposit_amount',
        'booking_type', 'cancellation_policy', 'allow_pets',
        'allow_smoking', 'allow_parties', 'check_in_time',
        'check_out_time', 'cover_photo', 'status', 'rating_avg',
        'rating_count', 'views_count', 'is_featured',
    ];

    protected $appends = ['cover_photo_url'];

    protected function casts(): array
    {
        return [
            'latitude'       => 'float',
            'longitude'      => 'float',
            'price_per_night'  => 'float',
            'price_per_week'   => 'float',
            'price_per_month'  => 'float',
            'deposit_amount'   => 'float',
            'rating_avg'       => 'float',
            'allow_pets'       => 'boolean',
            'allow_smoking'    => 'boolean',
            'allow_parties'    => 'boolean',
            'is_featured'      => 'boolean',
        ];
    }

    // ─── Relationships ────────────────────────────────────────────────────────

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function photos()
    {
        return $this->hasMany(PropertyPhoto::class)->orderBy('sort_order');
    }

    public function amenities()
    {
        return $this->hasMany(PropertyAmenity::class);
    }

    public function blockedDates()
    {
        return $this->hasMany(PropertyBlockedDate::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class)->where('type', 'tenant_to_property');
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByCity($query, string $city)
    {
        return $query->where('city', $city);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByPriceRange($query, ?float $min, ?float $max)
    {
        if ($min !== null) {
            $query->where('price_per_night', '>=', $min);
        }
        if ($max !== null) {
            $query->where('price_per_night', '<=', $max);
        }
        return $query;
    }

    public function scopeAvailableBetween($query, string $start, string $end)
    {
        return $query->whereDoesntHave('blockedDates', function ($q) use ($start, $end) {
            $q->where('start_date', '<=', $end)
              ->where('end_date', '>=', $start);
        })->whereDoesntHave('bookings', function ($q) use ($start, $end) {
            $q->whereIn('status', ['confirmed', 'pending'])
              ->where('check_in', '<=', $end)
              ->where('check_out', '>=', $start);
        });
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    public function getCoverPhotoUrlAttribute(): string
    {
        return $this->coverPhotoUrl();
    }

    public function coverPhotoUrl(): string
    {
        // Check photos relationship for cover photo
        $cover = $this->photos()->where('is_cover', true)->first()
                 ?? $this->photos()->first();

        if ($cover) {
            return asset('storage/' . $cover->path);
        }

        if ($this->cover_photo) {
            return asset('storage/' . $this->cover_photo);
        }

        return asset('images/property-placeholder.jpg');
    }

    public function priceFor(int $nights): array
    {
        $subtotal   = $nights * $this->price_per_night;
        $serviceFee = round($subtotal * 0.03, 0);
        $commission = round($subtotal * 0.08, 0);
        $total      = $subtotal + $serviceFee + $this->deposit_amount;

        return [
            'nights'      => $nights,
            'subtotal'    => $subtotal,
            'service_fee' => $serviceFee,
            'commission'  => $commission,
            'deposit'     => $this->deposit_amount,
            'total'       => $total,
        ];
    }

    public function isAvailable(string $start, string $end): bool
    {
        $startDate = Carbon::parse($start);
        $endDate   = Carbon::parse($end);

        // Check blocked dates
        $blocked = $this->blockedDates()
            ->where('start_date', '<=', $endDate->toDateString())
            ->where('end_date', '>=', $startDate->toDateString())
            ->exists();

        if ($blocked) {
            return false;
        }

        // Check existing confirmed bookings
        $booked = $this->bookings()
            ->whereIn('status', ['confirmed', 'pending'])
            ->where('check_in', '<=', $endDate->toDateString())
            ->where('check_out', '>=', $startDate->toDateString())
            ->exists();

        return !$booked;
    }

    public function typeLabel(): string
    {
        return match($this->type) {
            'studio'    => 'Studio',
            'apartment' => 'Appartement',
            'villa'     => 'Villa',
            'room'      => 'Chambre',
            'duplex'    => 'Duplex',
            default     => ucfirst($this->type),
        };
    }
}
