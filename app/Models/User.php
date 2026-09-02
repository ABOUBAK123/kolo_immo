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
        'agent_code',
        'referred_by_agent_id',
        'kyc_status',
        'avatar',
        'country',
        'language',
        'currency',
        'google_id',
        'facebook_id',
        'github_id',
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
            'trust_score'        => 'float',
        ];
    }

    // ─── Relationships ────────────────────────────────────────────────────────

    public function properties()
    {
        return $this->hasMany(Property::class, 'owner_id');
    }

    public function subscriptions()
    {
        return $this->hasMany(\App\Models\Subscription::class);
    }

    public function activeSubscription(): ?\App\Models\Subscription
    {
        return $this->subscriptions()
            ->where('status', 'active')
            ->where('ends_at', '>=', now())
            ->with('plan')
            ->latest()
            ->first();
    }

    public function currentPlan(): ?\App\Models\Plan
    {
        return $this->activeSubscription()?->plan;
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

    /** The agent who referred this user at registration (via agent code), if any. */
    public function referredByAgent()
    {
        return $this->belongsTo(User::class, 'referred_by_agent_id');
    }

    /** Users (owners/tenants) this agent has referred via their agent code. */
    public function referredUsers()
    {
        return $this->hasMany(User::class, 'referred_by_agent_id');
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    public function favoriteProperties()
    {
        return $this->belongsToMany(Property::class, 'favorites')
                    ->withPivot('price_at_save')
                    ->withTimestamps();
    }

    public function wallet()
    {
        return $this->hasOne(Wallet::class);
    }

    public function searchAlerts()
    {
        return $this->hasMany(SearchAlert::class);
    }

    /** Get or create the user's wallet. */
    public function getOrCreateWallet(): Wallet
    {
        return $this->wallet ?? $this->wallet()->create([
            'balance'  => 0,
            'currency' => 'XOF',
        ]);
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

    public function isAgent(): bool
    {
        return $this->role === 'agent';
    }

    public function isKycVerified(): bool
    {
        return $this->kyc_status === 'verified';
    }

    /** Generate a unique agent referral code, e.g. "AGT-K3F9QX". */
    public static function generateAgentCode(): string
    {
        do {
            $code = 'AGT-' . strtoupper(\Illuminate\Support\Str::random(6));
        } while (self::where('agent_code', $code)->exists());

        return $code;
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
