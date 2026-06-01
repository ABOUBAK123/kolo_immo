<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    protected $fillable = [
        'name', 'slug', 'description', 'price_monthly',
        'max_properties', 'max_photos_per_property',
        'featured_listing', 'analytics_advanced', 'priority_support',
        'is_active', 'sort_order',
    ];

    protected $casts = [
        'featured_listing'   => 'boolean',
        'analytics_advanced' => 'boolean',
        'priority_support'   => 'boolean',
        'is_active'          => 'boolean',
    ];

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function isFree(): bool
    {
        return $this->price_monthly === 0;
    }

    public function formattedPrice(): string
    {
        return $this->price_monthly === 0
            ? 'Gratuit'
            : number_format($this->price_monthly, 0, ',', ' ') . ' FCFA/mois';
    }
}
