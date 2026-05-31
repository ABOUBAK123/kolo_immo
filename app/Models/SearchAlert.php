<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SearchAlert extends Model
{
    protected $fillable = [
        'user_id', 'name', 'filters', 'frequency', 'is_active', 'last_sent_at',
    ];

    protected $casts = [
        'filters'      => 'array',
        'is_active'    => 'boolean',
        'last_sent_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function filterLabel(): string
    {
        $f = $this->filters;
        $parts = [];
        if (!empty($f['city']))      $parts[] = $f['city'];
        if (!empty($f['type']))      $parts[] = ucfirst($f['type']);
        if (!empty($f['price_max'])) $parts[] = '≤ ' . number_format((float) $f['price_max'], 0, ',', ' ') . ' FCFA';
        if (!empty($f['guests']))    $parts[] = $f['guests'] . ' pers.';
        return $parts ? implode(' · ', $parts) : 'Tous les biens';
    }

    public function isDue(): bool
    {
        if (!$this->is_active) return false;
        if (!$this->last_sent_at) return true;

        return match($this->frequency) {
            'daily'  => $this->last_sent_at->lt(now()->subDay()),
            'weekly' => $this->last_sent_at->lt(now()->subWeek()),
            default  => false,
        };
    }

    /** Build a Property query matching this alert's filters. */
    public function buildQuery()
    {
        $f = $this->filters;
        $query = Property::active()->with(['photos']);

        if (!empty($f['city']))      $query->byCity($f['city']);
        if (!empty($f['type']))      $query->byType($f['type']);
        if (!empty($f['price_min'])) $query->where('price_per_night', '>=', (float) $f['price_min']);
        if (!empty($f['price_max'])) $query->where('price_per_night', '<=', (float) $f['price_max']);
        if (!empty($f['guests']))    $query->where('capacity', '>=', (int) $f['guests']);

        return $query;
    }
}
