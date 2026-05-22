<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role',
        'kyc_status',
        'avatar',
        'country',
        'city',
        'trust_score',
        'is_active',
        'is_banned',
        'email_verified_at',
        'phone_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at'  => 'datetime',
            'phone_verified_at'  => 'datetime',
            'password'           => 'hashed',
            'is_active'          => 'boolean',
            'is_banned'          => 'boolean',
            'trust_score'        => 'integer',
        ];
    }

    // ─── Relationships ────────────────────────────────────────────────────────

    public function properties()
    {
        return $this->hasMany(Property::class, 'owner_id');
    }

    public function bookingsAsTenant()
    {
        return $this->hasMany(Booking::class, 'tenant_id');
    }

    public function bookingsAsOwner()
    {
        return $this->hasMany(Booking::class, 'owner_id');
    }

    public function reviewsGiven()
    {
        return $this->hasMany(Review::class, 'reviewer_id');
    }

    public function reviewsReceived()
    {
        return $this->hasMany(Review::class, 'reviewee_id');
    }

    public function conversationsAsTenant()
    {
        return $this->hasMany(Conversation::class, 'tenant_id');
    }

    public function conversationsAsOwner()
    {
        return $this->hasMany(Conversation::class, 'owner_id');
    }

    public function kycDocuments()
    {
        return $this->hasMany(KycDocument::class);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isOwner(): bool
    {
        return in_array($this->role, ['owner', 'both', 'admin']);
    }

    public function isTenant(): bool
    {
        return in_array($this->role, ['tenant', 'both', 'admin']);
    }

    public function isKycVerified(): bool
    {
        return $this->kyc_status === 'verified';
    }

    public function avatarUrl(): string
    {
        if ($this->avatar) {
            return asset('storage/' . $this->avatar);
        }

        $initials = urlencode(mb_strtoupper(mb_substr($this->name, 0, 1)));
        return "https://ui-avatars.com/api/?name={$initials}&background=F59E0B&color=fff&size=128";
    }
}
