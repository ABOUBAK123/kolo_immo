<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Rapport financier {{ $year }} — {{ $user->name }}</title>
<style>
@media print { body { -webkit-print-color-adjust: exact; } }
body { font-family: Arial, sans-serif; color: #111; margin: 0; padding: 24px; font-size: 13px; }
.header { display: flex; justify-content: space-between; align-items: flex-start; padding-bottom: 16px; border-bottom: 2px solid #1B4F72; margin-bottom: 20px; }
.logo { font-size: 22px; font-weight: 800; color: #1B4F72; }
.logo span { color: #F59E0B; }
h1 { font-size: 18px; margin: 0 0 4px; }
.subtitle { color: #6b7280; font-size: 12px; }
.summary { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-bottom: 20px; }
.sum-card { background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 8px; padding: 12px; text-align: center; }
.sum-card .val { font-size: 18px; font-weight: 800; color: #1B4F72; }
.sum-card .lab { font-size: 11px; color: #6b7280; margin-top: 2px; }
.monthly { margin-bottom: 20px; }
.bar-row { display: flex; align-items: center; gap: 8px; margin-bottom: 6px; }
.bar-label { width: 80px; font-size: 11px; color: #374151; flex-shrink: 0; text-align: right; }
.bar-outer { flex: 1; height: 16px; background: #f3f4f6; border-radius: 4px; overflow: hidden; }
.bar-inner { height: 100%; background: #1B4F72; border-radius: 4px; }
.bar-val { width: 100px; font-size: 11px; font-weight: 700; color: #1B4F72; flex-shrink: 0; }
table { width: 100%; border-collapse: collapse; font-size: 11px; }
th { background: #1B4F72; color: #fff; padding: 7px 8px; text-align: left; }
td { padding: 6px 8px; border-bottom: 1px solid #f3f4f6; }
tr:nth-child(even) td { background: #fafafa; }
.total-row td { font-weight: 800; background: #f0f9ff; border-top: 2px solid #1B4F72; }
.footer { margin-top: 20px; padding-top: 12px; border-top: 1px solid #e5e7eb; color: #9ca3af; font-size: 10px; text-align: center; }
.btn-print { position: fixed; bottom: 20px; right: 20px; background: #1B4F72; color: #fff; border: none; padding: 12px 20px; border-radius: 10px; font-weight: 700; cursor: pointer; font-size: 13px; }
@media print { .btn-print { display: none; } }
</style>
</head>
<body>

<div class="header">
    <div>
        <div class="logo">Kolo <span>Immo</span></div>
        <p class="subtitle">Résidences meublées en Afrique de l'Ouest</p>
    </div>
    <div style="text-align:right">
        <h1>Rapport financier {{ $year }}</h1>
        <p class="subtitle">{{ $user->name }} · Généré le {{ now()->format('d/m/Y à H:i') }}</p>
    </div>
</div>

{{-- Summary --}}
<div class="summary">
    <div class="sum-card">
        <div class="val">{{ $bookings->count() }}</div>
        <div class="lab">Réservations</div>
    </div>
    <div class="sum-card">
        <div class="val">{{ number_format($totalRevenue, 0, ',', ' ') }}</div>
        <div class="lab">Total FCFA</div>
    </div>
    <div class="sum-card">
        <div class="val">{{ $bookings->count() > 0 ? number_format($totalRevenue / $bookings->count(), 0, ',', ' ') : 0 }}</div>
        <div class="lab">Moy. / réservation</div>
    </div>
</div>

{{-- Monthly bars --}}
@php $maxAmount = collect($byMonth)->max('amount') ?: 1; @endphp
<div class="monthly">
    <p style="font-weight:700;margin-bottom:10px;font-size:13px">Revenus par mois</p>
    @foreach($byMonth as $m => $data)
    @if($data['amount'] > 0)
    <div class="bar-row">
        <span class="bar-label">{{ $data['label'] }}</span>
        <div class="bar-outer">
            <div class="bar-inner" style="width:{{ round(($data['amount'] / $maxAmount) * 100) }}%"></div>
        </div>
        <span class="bar-val">{{ number_format($data['amount'], 0, ',', ' ') }} F</span>
    </div>
    @endif
    @endforeach
</div>

{{-- Booking table --}}
<table>
    <thead>
        <tr>
            <th>Référence</th><th>Bien</th><th>Locataire</th>
            <th>Arrivée</th><th>Départ</th><th>Nuits</th><th>Montant (FCFA)</th>
        </tr>
    </thead>
    <tbody>
        @foreach($bookings as $b)
        <tr>
            <td style="font-family:monospace">{{ $b->reference }}</td>
            <td>{{ \Illuminate\Support\Str::limit($b->property?->title ?? '—', 28) }}</td>
            <td>{{ $b->tenant?->name ?? '—' }}</td>
            <td>{{ $b->check_in->format('d/m/Y') }}</td>
            <td>{{ $b->check_out->format('d/m/Y') }}</td>
            <td style="text-align:center">{{ $b->nights }}</td>
            <td style="font-weight:700">{{ number_format($b->total_amount, 0, ',', ' ') }}</td>
        </tr>
        @endforeach
        <tr class="total-row">
            <td colspan="6">TOTAL</td>
            <td>{{ number_format($totalRevenue, 0, ',', ' ') }}</td>
        </tr>
    </tbody>
</table>

<div class="footer">Kolo Immo — Ce rapport est généré automatiquement. Toutes les valeurs sont en FCFA.</div>

<button class="btn-print" onclick="window.print()">🖨 Imprimer / Sauvegarder PDF</button>
</body>
</html>
