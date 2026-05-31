<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Dispute extends Model
{
    protected $fillable = [
        'booking_id', 'opened_by', 'against_user_id',
        'reason', 'description', 'status', 'resolution',
        'admin_notes', 'resolved_by', 'resolved_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    // ─── Labels ───────────────────────────────────────────────────────────────

    const REASONS = [
        'non_payment'      => 'Non-paiement',
        'damage'           => 'Dégradation du bien',
        'fraud'            => 'Fraude / escroquerie',
        'misrepresentation'=> 'Bien non conforme à l\'annonce',
        'refund_refused'   => 'Remboursement refusé',
        'other'            => 'Autre',
    ];

    const STATUSES = [
        'open'        => ['label' => 'Ouvert',      'color' => 'red'],
        'in_progress' => ['label' => 'En cours',    'color' => 'yellow'],
        'resolved'    => ['label' => 'Résolu',      'color' => 'green'],
        'closed'      => ['label' => 'Clôturé',     'color' => 'gray'],
    ];

    const RESOLUTIONS = [
        'refund_full'    => 'Remboursement total',
        'refund_partial' => 'Remboursement partiel',
        'no_action'      => 'Aucune action',
    ];

    // ─── Relations ────────────────────────────────────────────────────────────

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function openedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'opened_by');
    }

    public function againstUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'against_user_id');
    }

    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function evidences(): HasMany
    {
        return $this->hasMany(DisputeEvidence::class);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    public function isOpen(): bool       { return $this->status === 'open'; }
    public function isInProgress(): bool { return $this->status === 'in_progress'; }
    public function isResolved(): bool   { return in_array($this->status, ['resolved', 'closed']); }

    public function reasonLabel(): string
    {
        return self::REASONS[$this->reason] ?? $this->reason;
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status]['label'] ?? $this->status;
    }

    public function statusColor(): string
    {
        return self::STATUSES[$this->status]['color'] ?? 'gray';
    }
}
