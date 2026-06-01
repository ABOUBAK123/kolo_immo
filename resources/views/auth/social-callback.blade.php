<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion sociale</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, sans-serif; background: #f3f4f6; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .card { background: white; border-radius: 16px; padding: 32px; max-width: 360px; width: 90%; text-align: center; box-shadow: 0 4px 24px rgba(0,0,0,0.08); }
        .icon { font-size: 52px; margin-bottom: 16px; }
        h2 { font-size: 20px; font-weight: 700; color: #111; margin-bottom: 8px; }
        p  { font-size: 14px; color: #6b7280; }
    </style>
</head>
<body>
<div class="card">
    @if($success)
    <div class="icon">✅</div>
    <h2>Connexion réussie !</h2>
    <p>Retour à l'application...</p>
    @else
    <div class="icon">❌</div>
    <h2>Échec de la connexion</h2>
    <p>{{ $error ?? 'Une erreur est survenue.' }}</p>
    @endif
</div>

<script>
    // Données envoyées à l'application mobile via window.ReactNativeWebView
    const payload = {
        success: {{ $success ? 'true' : 'false' }},
        @if($success)
        token: "{{ $token }}",
        user:  {!! json_encode($user) !!},
        @else
        error: "{{ $error ?? 'Erreur inconnue' }}",
        @endif
    };

    // Notifier le WebView React Native
    if (window.ReactNativeWebView) {
        window.ReactNativeWebView.postMessage(JSON.stringify(payload));
    }

    // Fallback: modifier l'URL pour que le WebView puisse l'intercepter
    setTimeout(() => {
        window.location.href = 'koloimmo://auth/social/callback?data=' + encodeURIComponent(JSON.stringify(payload));
    }, 300);
</script>
</body>
</html>
