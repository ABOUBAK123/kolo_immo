<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OtpCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'phone', 'email', 'code', 'purpose',
        'otpable_type', 'otpable_id',
        'used', 'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'used'       => 'boolean',
            'expires_at' => 'datetime',
        ];
    }

    // ─── Relationships ────────────────────────────────────────────────────────

    public function otpable()
    {
        return $this->morphTo();
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isValid(): bool
    {
        return !$this->used && !$this->isExpired();
    }

    public function markAsUsed(): void
    {
        $this->update(['used' => true]);
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeValid($query)
    {
        return $query->where('used', false)
                     ->where('expires_at', '>', now());
    }

    public function scopeForPurpose($query, string $purpose)
    {
        return $query->where('purpose', $purpose);
    }
}
