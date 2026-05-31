<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Nouvelle alerte</title>
<style>
body{font-family:Arial,sans-serif;background:#f4f4f4;margin:0;padding:20px}
.container{max-width:560px;margin:0 auto;background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 4px 12px rgba(0,0,0,.1)}
.header{background:linear-gradient(135deg,#1B4F72,#3498DB);padding:28px 24px;text-align:center;color:#fff}
.header h1{margin:0;font-size:20px}
.body{padding:24px}
.prop-card{display:flex;gap:12px;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden;margin-bottom:12px}
.prop-img{width:90px;height:80px;object-fit:cover;flex-shrink:0;background:#dbeafe}
.prop-body{flex:1;padding:10px 12px}
.prop-title{font-weight:700;color:#111;font-size:13px;margin:0 0 4px}
.prop-city{color:#9ca3af;font-size:11px;margin:0 0 6px}
.prop-price{font-weight:800;color:#1B4F72;font-size:14px}
.cta{display:block;background:#F59E0B;color:#fff;text-decoration:none;font-weight:700;font-size:13px;padding:8px 12px;border-radius:8px;text-align:center;margin-top:6px;width:fit-content}
.footer{background:#f9fafb;padding:14px 24px;text-align:center;color:#9ca3af;font-size:11px}
</style>
</head>
<body>
<div class="container">
    <div class="header">
        <div style="font-size:32px;margin-bottom:8px">🔔</div>
        <h1>Nouvelles annonces pour vous !</h1>
        <p style="margin:6px 0 0;opacity:.85;font-size:13px">Alerte : {{ $alert->name }} · {{ $alert->filterLabel() }}</p>
    </div>
    <div class="body">
        <p style="color:#374151;font-size:14px;margin-top:0">
            {{ $properties->count() }} nouveau{{ $properties->count() > 1 ? 'x' : '' }} bien{{ $properties->count() > 1 ? 's' : '' }} correspond{{ $properties->count() > 1 ? 'ent' : '' }} à votre recherche :
        </p>

        @foreach($properties as $p)
        <div class="prop-card">
            @if($p->cover_photo_url)
            <img src="{{ $p->cover_photo_url }}" class="prop-img" alt="{{ $p->title }}">
            @else
            <div class="prop-img" style="display:flex;align-items:center;justify-content:center;font-size:28px">🏠</div>
            @endif
            <div class="prop-body">
                <p class="prop-title">{{ Str::limit($p->title, 40) }}</p>
                <p class="prop-city">📍 {{ $p->city }}</p>
                <p class="prop-price">{{ number_format($p->price_per_night, 0, ',', ' ') }} FCFA <span style="font-weight:400;font-size:11px;color:#9ca3af">/nuit</span></p>
                <a href="{{ url('/properties/' . $p->id) }}" class="cta">Voir →</a>
            </div>
        </div>
        @endforeach

        <a href="{{ url('/search-alerts') }}" style="display:block;text-align:center;color:#6b7280;font-size:12px;margin-top:16px;text-decoration:none">
            Gérer mes alertes
        </a>
    </div>
    <div class="footer">
        Vous recevez cet email car vous avez une alerte active sur Kolo Immo.<br>
        <a href="{{ url('/search-alerts') }}" style="color:#3b82f6">Se désabonner</a>
    </div>
</div>
</body>
</html>
