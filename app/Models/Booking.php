<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference', 'property_id', 'tenant_id', 'owner_id',
        'check_in', 'check_out', 'nights', 'guests',
        'price_per_night', 'subtotal', 'service_fee',
        'platform_commission', 'vat_percent', 'vat_amount',
        'deposit_amount', 'total_amount',
        'status', 'payment_status', 'special_requests',
        'confirmed_at', 'cancelled_at', 'cancellation_reason',
        'cancelled_by', 'checked_in_at', 'checked_out_at',
        'funds_released_at',
    ];

    protected function casts(): array
    {
        return [
            'check_in'          => 'date',
            'check_out'         => 'date',
            'confirmed_at'      => 'datetime',
            'cancelled_at'      => 'datetime',
            'checked_in_at'     => 'datetime',
            'checked_out_at'    => 'datetime',
            'funds_released_at' => 'datetime',
            'price_per_night'   => 'float',
            'subtotal'          => 'float',
            'service_fee'       => 'float',
            'platform_commission' => 'float',
            'vat_percent'       => 'float',
            'vat_amount'        => 'float',
            'deposit_amount'    => 'float',
            'total_amount'      => 'float',
            'nights'            => 'integer',
            'guests'            => 'integer',
        ];
    }

    // ─── Relationships ────────────────────────────────────────────────────────

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function tenant()
    {
        return $this->belongsTo(User::class, 'tenant_id');
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function payment()
    {
        return $this->hasOne(Payment::class)->latest();
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function contract()
    {
        return $this->hasOne(Contract::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function conversation()
    {
        return $this->hasOne(Conversation::class);
    }

    public function disputes()
    {
        return $this->hasMany(Dispute::class);
    }

    public function renewals()
    {
        return $this->hasMany(ContractRenewal::class);
    }

    public function pendingRenewal()
    {
        return $this->hasOne(ContractRenewal::class)->where('status', 'pending')->latest();
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function scopeUpcoming($query)
    {
        return $query->whereIn('status', ['confirmed', 'pending'])
                     ->where('check_in', '>=', now()->toDateString());
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    public static function generateReference(): string
    {
        do {
            $ref = 'KI-' . strtoupper(Str::random(8));
        } while (static::where('reference', $ref)->exists());

        return $ref;
    }

    public function totalAmountDisplay(): string
    {
        return number_format($this->total_amount, 0, ',', ' ') . ' FCFA';
    }

    public function subtotalDisplay(): string
    {
        return number_format($this->subtotal, 0, ',', ' ') . ' FCFA';
    }

    public function statusLabel(): string
    {
        return match($this->status) {
            'pending'        => 'En attente',
            'confirmed'      => 'Confirmée',
            'cancelled'      => 'Annulée',
            'completed'      => 'Terminée',
            'refund_pending' => 'Remboursement en cours',
            'refunded'       => 'Remboursée',
            'disputed'       => 'Litige',
            default          => ucfirst($this->status),
        };
    }

    public function statusColor(): string
    {
        return match($this->status) {
            'pending'        => 'yellow',
            'confirmed'      => 'green',
            'cancelled'      => 'red',
            'completed'      => 'blue',
            'refund_pending' => 'orange',
            'refunded'       => 'gray',
            'disputed'       => 'red',
            default          => 'gray',
        };
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isConfirmed(): bool
    {
        return $this->status === 'confirmed';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isCancellable(): bool
    {
        return in_array($this->status, ['pending', 'confirmed']);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($booking) {
            if (empty($booking->reference)) {
                $booking->reference = static::generateReference();
            }
        });
    }
}
