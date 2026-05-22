<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contract extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference', 'booking_id', 'tenant_id', 'owner_id', 'pdf_path',
        'status', 'tenant_signed_at', 'tenant_signature_otp',
        'owner_signed_at', 'owner_signature_otp',
        'entry_inspection_path', 'exit_inspection_path',
        'entry_inspection_signed_at', 'exit_inspection_signed_at',
    ];

    protected $hidden = [
        'tenant_signature_otp',
        'owner_signature_otp',
    ];

    protected function casts(): array
    {
        return [
            'tenant_signed_at'            => 'datetime',
            'owner_signed_at'             => 'datetime',
            'entry_inspection_signed_at'  => 'datetime',
            'exit_inspection_signed_at'   => 'datetime',
        ];
    }

    // ─── Relationships ────────────────────────────────────────────────────────

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function tenant()
    {
        return $this->belongsTo(User::class, 'tenant_id');
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    public function isSigned(): bool
    {
        return $this->status === 'signed';
    }

    public function isSignedByTenant(): bool
    {
        return $this->tenant_signed_at !== null;
    }

    public function isSignedByOwner(): bool
    {
        return $this->owner_signed_at !== null;
    }

    public function statusLabel(): string
    {
        $labels = [
            'draft'             => 'Brouillon',
            'pending_signature' => 'En attente de signature',
            'signed'            => 'Signe',
            'expired'           => 'Expire',
        ];
        return $labels[$this->status] ?? ucfirst($this->status);
    }

    public function pdfUrl(): ?string
    {
        return $this->pdf_path ? asset('storage/' . $this->pdf_path) : null;
    }
}
