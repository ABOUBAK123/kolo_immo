<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id', 'booking_id', 'user_id', 'amount', 'currency',
        'method', 'phone_number', 'status', 'type',
        'cinetpay_transaction_id', 'gateway_response', 'failure_reason', 'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'amount'           => 'float',
            'paid_at'          => 'datetime',
            'gateway_response' => 'array',
        ];
    }

    // ─── Relationships ────────────────────────────────────────────────────────

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    public function amountDisplay(): string
    {
        return number_format($this->amount, 0, ',', ' ') . ' FCFA';
    }

    public function methodLabel(): string
    {
        $labels = [
            'orange_money'  => 'Orange Money',
            'wave'          => 'Wave',
            'mtn_money'     => 'MTN Money',
            'moov_money'    => 'Moov Money',
            'card'          => 'Carte bancaire',
            'bank_transfer' => 'Virement bancaire',
        ];
        return $labels[$this->method] ?? ucfirst($this->method);
    }

    public function statusLabel(): string
    {
        $labels = [
            'pending'    => 'En attente',
            'processing' => 'En cours',
            'success'    => 'Reussi',
            'failed'     => 'Echoue',
            'refunded'   => 'Rembourse',
        ];
        return $labels[$this->status] ?? ucfirst($this->status);
    }

    public function isSuccessful(): bool
    {
        return $this->status === 'success';
    }

    public function typeLabel(): string
    {
        $labels = [
            'payment' => 'Paiement',
            'refund'  => 'Remboursement',
            'release' => 'Liberation de fonds',
        ];
        return $labels[$this->type] ?? ucfirst($this->type);
    }
}
