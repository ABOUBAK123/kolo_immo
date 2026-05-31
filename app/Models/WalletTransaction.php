<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WalletTransaction extends Model
{
    protected $fillable = [
        'wallet_id', 'type', 'amount', 'balance_after',
        'description', 'reference', 'booking_id', 'status', 'metadata',
    ];

    protected $casts = [
        'amount'        => 'decimal:2',
        'balance_after' => 'decimal:2',
        'metadata'      => 'array',
    ];

    const TYPE_ICONS = [
        'credit'  => ['icon' => '↓', 'color' => 'green',  'label' => 'Crédit'],
        'topup'   => ['icon' => '+', 'color' => 'blue',   'label' => 'Recharge'],
        'debit'   => ['icon' => '↑', 'color' => 'red',    'label' => 'Paiement'],
        'refund'  => ['icon' => '↺', 'color' => 'purple', 'label' => 'Remboursement'],
    ];

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function typeInfo(): array
    {
        return self::TYPE_ICONS[$this->type] ?? ['icon' => '?', 'color' => 'gray', 'label' => ucfirst($this->type)];
    }

    public function isCredit(): bool
    {
        return in_array($this->type, ['credit', 'topup', 'refund']);
    }
}
