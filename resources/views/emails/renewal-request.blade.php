<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8"><title>Demande de renouvellement</title>
<style>
body{font-family:Arial,sans-serif;background:#f4f4f4;margin:0;padding:20px}
.c{max-width:520px;margin:0 auto;background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 4px 12px rgba(0,0,0,.1)}
.h{background:linear-gradient(135deg,#16a34a,#22c55e);padding:28px 24px;text-align:center;color:#fff}
.b{padding:24px}.info{background:#f0fdf4;border:1px solid #86efac;border-radius:12px;padding:16px;margin:16px 0}
.btn{display:inline-block;background:#16a34a;color:#fff;text-decoration:none;font-weight:700;font-size:14px;padding:12px 20px;border-radius:10px;text-align:center;margin-right:10px}
.f{background:#f9fafb;padding:14px 24px;text-align:center;color:#9ca3af;font-size:11px}
</style></head><body>
<div class="c">
<div class="h">
    <div style="font-size:36px;margin-bottom:8px">🔄</div>
    <h1 style="margin:0;font-size:20px">Demande de renouvellement</h1>
</div>
<div class="b">
    <p>Bonjour {{ $recipient->name }},</p>
    <p><strong>{{ $renewal->initiatedBy->name }}</strong> vous propose de prolonger le séjour à <strong>{{ $booking->property->title }}</strong>.</p>
    <div class="info">
        <p style="margin:0;font-size:13px">📅 Date de fin actuelle : <strong>{{ $renewal->current_end_date->format('d/m/Y') }}</strong></p>
        <p style="margin:6px 0 0;font-size:13px">📅 Nouvelle date proposée : <strong>{{ $renewal->proposed_end_date->format('d/m/Y') }}</strong></p>
        <p style="margin:6px 0 0;font-size:13px">🌙 Nuits supplémentaires : <strong>{{ $renewal->additional_nights }}</strong></p>
        <p style="margin:6px 0 0;font-size:13px">💰 Montant/nuit proposé : <strong>{{ number_format($renewal->proposed_amount, 0, ',', ' ') }} FCFA</strong></p>
        @if($renewal->note)
        <p style="margin:8px 0 0;font-size:13px;color:#374151;border-top:1px solid #bbf7d0;padding-top:8px">💬 {{ $renewal->note }}</p>
        @endif
    </div>
    <p style="font-size:14px;color:#374151">Connectez-vous pour accepter ou refuser cette demande.</p>
    <a href="{{ url('/renewals/' . $renewal->id) }}" class="btn">Voir la demande →</a>
</div>
<div class="f">Kolo Immo — Résidences meublées en Afrique de l'Ouest</div>
</div></body></html>
