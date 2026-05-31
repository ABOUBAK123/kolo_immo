<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Baisse de prix</title>
<style>
body{font-family:Arial,sans-serif;background:#f4f4f4;margin:0;padding:20px}
.container{max-width:520px;margin:0 auto;background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 4px 12px rgba(0,0,0,.1)}
.header{background:linear-gradient(135deg,#1B4F72,#3498DB);padding:32px 24px;text-align:center;color:#fff}
.header h1{margin:0;font-size:24px}
.body{padding:24px}
.price-box{background:#f0fdf4;border:2px solid #86efac;border-radius:12px;padding:20px;text-align:center;margin:16px 0}
.old-price{color:#9ca3af;text-decoration:line-through;font-size:16px}
.new-price{color:#16a34a;font-size:32px;font-weight:800;margin:4px 0}
.saving{background:#22c55e;color:#fff;padding:4px 12px;border-radius:20px;font-size:13px;font-weight:600;display:inline-block}
.cta{display:block;background:#F59E0B;color:#fff;text-decoration:none;font-weight:700;font-size:16px;padding:14px;border-radius:10px;text-align:center;margin:20px 0}
.footer{background:#f9fafb;padding:16px 24px;text-align:center;color:#9ca3af;font-size:12px}
</style>
</head>
<body>
<div class="container">
    <div class="header">
        <div style="font-size:40px;margin-bottom:8px">🏠</div>
        <h1>Bonne nouvelle, {{ $user->name }} !</h1>
        <p style="margin:8px 0 0;opacity:.85">Un de vos favoris vient de baisser de prix</p>
    </div>
    <div class="body">
        <p style="color:#374151;margin-top:0">Le bien que vous avez mis en favori est maintenant moins cher :</p>
        <p style="font-weight:700;font-size:18px;color:#111827;margin-bottom:4px">{{ $property->title }}</p>
        <p style="color:#6b7280;font-size:14px;margin-top:0">📍 {{ $property->city }}</p>

        <div class="price-box">
            <p class="old-price">Ancien prix : {{ number_format($oldPrice, 0, ',', ' ') }} FCFA / nuit</p>
            <p class="new-price">{{ number_format($newPrice, 0, ',', ' ') }} FCFA / nuit</p>
            <span class="saving">- {{ number_format($oldPrice - $newPrice, 0, ',', ' ') }} FCFA d'économie</span>
        </div>

        <p style="color:#6b7280;font-size:14px">Profitez-en avant que le prix ne remonte !</p>

        <a href="{{ url('/properties/' . $property->id) }}" class="cta">
            Voir le bien →
        </a>
    </div>
    <div class="footer">
        Vous recevez cet email car vous avez ce bien dans vos favoris sur Kolo Immo.<br>
        <a href="{{ url('/favorites') }}" style="color:#3b82f6">Gérer mes favoris</a>
    </div>
</div>
</body>
</html>
