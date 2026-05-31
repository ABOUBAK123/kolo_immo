<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContractRenewal extends Model
{
    protected $fillable = [
        'booking_id', 'initiated_by', 'status',
        'current_amount', 'proposed_amount',
        'current_end_date', 'proposed_end_date', 'additional_nights',
        'note', 'responded_at', 'responded_by',
    ];

    protected $casts = [
        'current_end_date'  => 'date',
        'proposed_end_date' => 'date',
        'responded_at'      => 'datetime',
        'current_amount'    => 'decimal:2',
        'proposed_amount'   => 'decimal:2',
    ];

    public function booking(): BelongsTo   { return $this->belongsTo(Booking::class); }
    public function initiatedBy(): BelongsTo { return $this->belongsTo(User::class, 'initiated_by'); }
    public function respondedBy(): BelongsTo { return $this->belongsTo(User::class, 'responded_by'); }

    public function isPending(): bool  { return $this->status === 'pending'; }
    public function isAccepted(): bool { return $this->status === 'accepted'; }

    public function statusLabel(): string
    {
        return match($this->status) {
            'pending'  => 'En attente',
            'accepted' => 'Accepté',
            'rejected' => 'Refusé',
            'expired'  => 'Expiré',
            default    => ucfirst($this->status),
        };
    }

    public function statusColor(): string
    {
        return match($this->status) {
            'pending'  => 'yellow',
            'accepted' => 'green',
            'rejected' => 'red',
            'expired'  => 'gray',
            default    => 'gray',
        };
    }
}
