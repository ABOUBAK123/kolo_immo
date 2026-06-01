<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rappel de fin de séjour</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #f3f4f6; margin: 0; padding: 0; }
        .wrapper { max-width: 580px; margin: 32px auto; background: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .header { background: linear-gradient(135deg, #1d4ed8, #3b82f6); padding: 32px 32px 24px; text-align: center; }
        .header-icon { background: rgba(255,255,255,0.2); width: 56px; height: 56px; border-radius: 14px; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 12px; font-size: 28px; }
        .header h1 { color: #ffffff; font-size: 20px; font-weight: 700; margin: 0; }
        .header p { color: rgba(255,255,255,0.85); font-size: 14px; margin: 6px 0 0; }
        .body { padding: 32px; }
        .greeting { font-size: 15px; color: #374151; margin-bottom: 20px; }
        .alert-box { background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 12px; padding: 16px 20px; margin-bottom: 24px; }
        .alert-box .days { font-size: 28px; font-weight: 800; color: #1d4ed8; margin: 0; }
        .alert-box .label { font-size: 13px; color: #3b82f6; margin: 2px 0 0; }
        .property-card { border: 1px solid #e5e7eb; border-radius: 12px; padding: 16px 20px; margin-bottom: 24px; }
        .property-card h3 { font-size: 15px; font-weight: 700; color: #111827; margin: 0 0 12px; }
        .info-row { display: flex; align-items: center; gap: 8px; font-size: 13px; color: #6b7280; margin-bottom: 8px; }
        .info-row strong { color: #374151; }
        .badge { display: inline-block; background: #dcfce7; color: #15803d; font-size: 11px; font-weight: 600; padding: 2px 8px; border-radius: 20px; }
        .cta { text-align: center; margin: 28px 0; }
        .btn { display: inline-block; background: #1d4ed8; color: #ffffff; font-size: 14px; font-weight: 700; padding: 14px 28px; border-radius: 12px; text-decoration: none; }
        .checklist { background: #f9fafb; border-radius: 12px; padding: 16px 20px; margin-bottom: 24px; }
        .checklist h4 { font-size: 13px; font-weight: 700; color: #374151; margin: 0 0 10px; }
        .checklist ul { margin: 0; padding-left: 20px; }
        .checklist li { font-size: 13px; color: #6b7280; margin-bottom: 6px; }
        .footer { background: #f9fafb; border-top: 1px solid #e5e7eb; padding: 20px 32px; text-align: center; }
        .footer p { font-size: 12px; color: #9ca3af; margin: 0; }
        .footer a { color: #3b82f6; text-decoration: none; }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="header">
        <div class="header-icon">📅</div>
        <h1>Rappel de fin de séjour</h1>
        <p>{{ $booking->property->title }}</p>
    </div>

    <div class="body">
        <p class="greeting">Bonjour <strong>{{ $recipient->name }}</strong>,</p>

        <div class="alert-box">
            @if($daysLeft === 1)
            <p class="days">Demain</p>
            <p class="label">est le dernier jour du séjour</p>
            @else
            <p class="days">{{ $daysLeft }} jours</p>
            <p class="label">avant la fin du séjour (check-out)</p>
            @endif
        </div>

        <div class="property-card">
            <h3>{{ $booking->property->title }}</h3>
            <div class="info-row">
                <span>📍</span>
                <span>{{ $booking->property->city }}{{ $booking->property->district ? ', ' . $booking->property->district : '' }}</span>
            </div>
            <div class="info-row">
                <span>📅</span>
                <span>Check-out : <strong>{{ $booking->check_out->format('d/m/Y') }}</strong>
                    @if($booking->property->check_out_time)
                        à {{ $booking->property->check_out_time }}
                    @endif
                </span>
            </div>
            <div class="info-row">
                <span>🔖</span>
                <span>Référence : <strong>{{ $booking->reference }}</strong></span>
            </div>
            <div class="info-row">
                <span>🌙</span>
                <span>{{ $booking->nights }} nuit(s) · <span class="badge">{{ ucfirst($booking->status) }}</span></span>
            </div>
        </div>

        @if($recipientRole === 'tenant')
        <div class="checklist">
            <h4>✅ Checklist de départ</h4>
            <ul>
                <li>Laissez le logement dans l'état où vous l'avez trouvé</li>
                <li>Remettez les clés au propriétaire</li>
                <li>Signalez tout problème ou dommage constaté</li>
                <li>Récupérez tous vos effets personnels</li>
                <li>Laissez un avis sur votre séjour</li>
            </ul>
        </div>

        <div class="cta">
            <a href="{{ url('/bookings/' . $booking->id) }}" class="btn">Voir ma réservation</a>
        </div>
        @else
        <div class="checklist">
            <h4>✅ Actions à prévoir</h4>
            <ul>
                <li>Préparez l'inspection de sortie du logement</li>
                <li>Vérifiez l'état du bien avec le locataire</li>
                <li>Confirmez le départ et la remise des clés</li>
                <li>Laissez un avis sur le locataire</li>
            </ul>
        </div>

        <div class="cta">
            <a href="{{ url('/owner/bookings/' . $booking->id) }}" class="btn">Gérer la réservation</a>
        </div>
        @endif

        <p style="font-size: 13px; color: #6b7280; text-align: center;">
            Des questions ? Utilisez la messagerie intégrée de la plateforme.
        </p>
    </div>

    <div class="footer">
        <p>Kolo Immo · <a href="{{ url('/') }}">koloimmo.com</a></p>
        <p style="margin-top: 4px;">Vous recevez cet email car vous avez une réservation active sur Kolo Immo.</p>
    </div>
</div>
</body>
</html>
