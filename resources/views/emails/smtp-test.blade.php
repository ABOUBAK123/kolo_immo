<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test de configuration SMTP — Kolo Immo</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background-color: #f3f4f6; font-family: 'Inter', Arial, sans-serif; color: #1f2937; }
        .wrapper { max-width: 560px; margin: 40px auto; padding: 0 16px 40px; }
        .card { background: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,0.08); }
        .header { background: #1B4F72; padding: 32px 40px; text-align: center; }
        .header-logo { display: inline-flex; align-items: center; gap: 10px; }
        .logo-icon { width: 44px; height: 44px; background: #F59E0B; border-radius: 10px;
                     display: inline-flex; align-items: center; justify-content: center; }
        .logo-name { color: #ffffff; font-size: 22px; font-weight: 800; letter-spacing: -0.5px; }
        .logo-accent { color: #F59E0B; }
        .body { padding: 40px; }
        .status-badge { display: inline-flex; align-items: center; gap: 8px; background: #ECFDF5;
                        border: 1px solid #6EE7B7; border-radius: 10px; padding: 10px 18px;
                        font-size: 14px; font-weight: 700; color: #047857; margin-bottom: 24px; }
        .greeting { font-size: 16px; color: #374151; margin-bottom: 8px; }
        .intro { font-size: 15px; color: #6b7280; line-height: 1.6; margin-bottom: 24px; }
        .details { background: #F9FAFB; border: 1px solid #e5e7eb; border-radius: 12px; padding: 20px 24px; margin-bottom: 24px; }
        .details-title { font-size: 12px; font-weight: 700; color: #9ca3af; text-transform: uppercase;
                          letter-spacing: 0.5px; margin-bottom: 14px; }
        .details-row { display: flex; justify-content: space-between; padding: 8px 0;
                        border-bottom: 1px solid #eef0f2; font-size: 14px; }
        .details-row:last-child { border-bottom: none; }
        .details-label { color: #6b7280; }
        .details-value { color: #1f2937; font-weight: 600; }
        .divider { height: 1px; background: #e5e7eb; margin: 24px 0; }
        .footer-text { font-size: 12px; color: #9ca3af; line-height: 1.6; text-align: center; }
        .footer { background: #f9fafb; padding: 20px 40px; text-align: center;
                  border-top: 1px solid #e5e7eb; }
        .footer small { font-size: 12px; color: #9ca3af; }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="card">
        <!-- Header -->
        <div class="header">
            <div class="header-logo">
                <div class="logo-icon">
                    <svg width="22" height="22" viewBox="0 0 20 20" fill="white">
                        <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                    </svg>
                </div>
                <span class="logo-name">Kolo <span class="logo-accent">Immo</span></span>
            </div>
        </div>

        <!-- Body -->
        <div class="body">
            <div class="status-badge">✅ Configuration SMTP opérationnelle</div>

            <p class="greeting">Bonjour,</p>
            <p class="intro">
                Cet email confirme que la configuration SMTP de votre plateforme <strong>Kolo Immo</strong>
                fonctionne correctement. Les notifications automatiques (codes de vérification, rappels,
                confirmations de réservation…) seront bien délivrées à vos utilisateurs.
            </p>

            <div class="details">
                <p class="details-title">Détails de l'envoi</p>
                <div class="details-row">
                    <span class="details-label">Destinataire</span>
                    <span class="details-value">{{ $to }}</span>
                </div>
                <div class="details-row">
                    <span class="details-label">Serveur SMTP</span>
                    <span class="details-value">{{ $host }}</span>
                </div>
                <div class="details-row">
                    <span class="details-label">Date d'envoi</span>
                    <span class="details-value">{{ $sentAt }}</span>
                </div>
            </div>

            <div class="divider"></div>

            <p class="footer-text">
                Cet email a été envoyé automatiquement depuis les paramètres d'administration, merci de ne pas y répondre.<br>
                Pour toute assistance, contactez-nous sur <strong>support@koloimmo.com</strong>
            </p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <small>© {{ date('Y') }} Kolo Immo — Résidences meublées en Afrique de l'Ouest<br>
            Ce message a été envoyé suite à un test de configuration effectué par un administrateur.</small>
        </div>
    </div>
</div>
</body>
</html>
