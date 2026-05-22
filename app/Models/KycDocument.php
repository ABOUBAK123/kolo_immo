<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KycDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'type', 'document_path', 'selfie_path',
        'status', 'rejection_reason', 'verified_at', 'verified_by',
    ];

    protected function casts(): array
    {
        return [
            'verified_at' => 'datetime',
        ];
    }

    // ─── Relationships ────────────────────────────────────────────────────────

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    public function typeLabel(): string
    {
        return match($this->type) {
            'cni'              => "Carte Nationale d'Identité",
            'passport'         => 'Passeport',
            'residence_permit' => 'Titre de séjour',
            'title_deed'       => 'Titre de propriété',
            'lease'            => 'Contrat de bail',
            default            => ucfirst($this->type),
        };
    }

    public function statusLabel(): string
    {
        return match($this->status) {
            'pending'  => 'En attente',
            'approved' => 'Approuvé',
            'rejected' => 'Rejeté',
            default    => ucfirst($this->status),
        };
    }

    public function documentUrl(): string
    {
        return $this->document_path ? asset('storage/' . $this->document_path) : '#';
    }

    public function selfieUrl(): string
    {
        return $this->selfie_path ? asset('storage/' . $this->selfie_path) : '#';
    }
}
