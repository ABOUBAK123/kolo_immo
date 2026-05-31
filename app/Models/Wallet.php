<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Wallet extends Model
{
    protected $fillable = ['user_id', 'balance', 'currency'];

    protected $casts = ['balance' => 'decimal:2'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class)->latest();
    }

    // ─── Core operations (atomic) ─────────────────────────────────────────────

    public function credit(float $amount, string $description, ?int $bookingId = null, string $type = 'credit'): WalletTransaction
    {
        $this->increment('balance', $amount);
        $this->refresh();

        return $this->transactions()->create([
            'type'          => $type,
            'amount'        => $amount,
            'balance_after' => $this->balance,
            'description'   => $description,
            'reference'     => 'TXN-' . strtoupper(Str::random(10)),
            'booking_id'    => $bookingId,
            'status'        => 'completed',
        ]);
    }

    public function debit(float $amount, string $description, ?int $bookingId = null): WalletTransaction
    {
        if ($this->balance < $amount) {
            throw new \RuntimeException('Solde insuffisant.');
        }

        $this->decrement('balance', $amount);
        $this->refresh();

        return $this->transactions()->create([
            'type'          => 'debit',
            'amount'        => $amount,
            'balance_after' => $this->balance,
            'description'   => $description,
            'reference'     => 'TXN-' . strtoupper(Str::random(10)),
            'booking_id'    => $bookingId,
            'status'        => 'completed',
        ]);
    }

    public function hasSufficientBalance(float $amount): bool
    {
        return $this->balance >= $amount;
    }

    public function formattedBalance(): string
    {
        return number_format((float) $this->balance, 0, ',', ' ') . ' ' . ($this->currency ?? 'XOF');
    }
}
