<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8"><title>Rappel fin de séjour</title>
<style>
body{font-family:Arial,sans-serif;background:#f4f4f4;margin:0;padding:20px}
.c{max-width:520px;margin:0 auto;background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 4px 12px rgba(0,0,0,.1)}
.h{background:linear-gradient(135deg,#1B4F72,#3498DB);padding:28px 24px;text-align:center;color:#fff}
.b{padding:24px}.info{background:#f0f9ff;border:1px solid #bae6fd;border-radius:12px;padding:16px;margin:16px 0}
.btn{display:block;background:#F59E0B;color:#fff;text-decoration:none;font-weight:700;font-size:14px;padding:12px;border-radius:10px;text-align:center;margin:20px 0}
.f{background:#f9fafb;padding:14px 24px;text-align:center;color:#9ca3af;font-size:11px}
</style></head><body>
<div class="c">
<div class="h">
    <div style="font-size:36px;margin-bottom:8px">⏰</div>
    <h1 style="margin:0;font-size:20px">Fin de séjour dans {{ $daysLeft }} jours</h1>
</div>
<div class="b">
    <p>Bonjour {{ $recipient->name }},</p>
    <p>Votre séjour à <strong>{{ $booking->property->title }}</strong> se termine dans <strong>{{ $daysLeft }} jours</strong>, le <strong>{{ $booking->check_out->format('d/m/Y') }}</strong>.</p>
    <div class="info">
        <p style="margin:0;font-size:13px">📅 Fin de séjour : <strong>{{ $booking->check_out->format('d/m/Y') }}</strong></p>
        <p style="margin:6px 0 0;font-size:13px">📍 {{ $booking->property->city }}</p>
        <p style="margin:6px 0 0;font-size:13px">🔖 Réf. {{ $booking->reference }}</p>
    </div>
    <p style="font-size:14px;color:#374151">Souhaitez-vous prolonger votre séjour ? Vous pouvez faire une demande de renouvellement directement depuis votre espace.</p>
    <a href="{{ url('/bookings/' . $booking->id) }}" class="btn">Voir ma réservation →</a>
</div>
<div class="f">Kolo Immo — Résidences meublées en Afrique de l'Ouest</div>
</div></body></html>
