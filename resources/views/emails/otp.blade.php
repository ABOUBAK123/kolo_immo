<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Code de vérification — Kolo Immo</title>
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
        .greeting { font-size: 16px; color: #374151; margin-bottom: 8px; }
        .intro { font-size: 15px; color: #6b7280; line-height: 1.6; margin-bottom: 32px; }
        .code-section { text-align: center; margin-bottom: 32px; }
        .code-label { font-size: 13px; font-weight: 600; color: #9ca3af; text-transform: uppercase;
                      letter-spacing: 1px; margin-bottom: 12px; }
        .code-box { display: inline-block; background: #EBF5FB; border: 2px solid #3498DB;
                    border-radius: 16px; padding: 20px 48px; }
        .code { font-size: 42px; font-weight: 800; letter-spacing: 12px; color: #1B4F72;
                font-family: 'Courier New', monospace; }
        .expiry { display: inline-flex; align-items: center; gap: 6px; background: #FEF3C7;
                  border: 1px solid #FCD34D; border-radius: 8px; padding: 8px 16px;
                  font-size: 13px; color: #92400E; font-weight: 600; margin-top: 12px; }
        .warning { background: #FFF7ED; border: 1px solid #FED7AA; border-radius: 12px;
                   padding: 16px 20px; font-size: 13px; color: #9a3412; line-height: 1.5; margin-bottom: 24px; }
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
            @if($recipientName)
            <p class="greeting">Bonjour {{ $recipientName }},</p>
            @else
            <p class="greeting">Bonjour,</p>
            @endif

            <p class="intro">
                @if($purpose === 'phone_verify')
                    Merci de vous être inscrit sur <strong>Kolo Immo</strong>. Voici votre code pour vérifier votre compte :
                @elseif($purpose === 'password_reset')
                    Vous avez demandé à réinitialiser votre mot de passe. Utilisez ce code :
                @elseif($purpose === 'login')
                    Voici votre code de connexion sécurisée :
                @elseif($purpose === 'contract_sign')
                    Voici votre code pour signer votre contrat de location :
                @else
                    Voici votre code de sécurité Kolo Immo :
                @endif
            </p>

            <!-- Code -->
            <div class="code-section">
                <p class="code-label">Votre code</p>
                <div class="code-box">
                    <div class="code">{{ $code }}</div>
                </div>
                <div style="margin-top: 12px;">
                    <span class="expiry">⏱ Valable 10 minutes</span>
                </div>
            </div>

            <!-- Security warning -->
            <div class="warning">
                <strong>🔒 Sécurité :</strong> Ne partagez jamais ce code avec qui que ce soit.
                Kolo Immo ne vous demandera jamais votre code par téléphone ou email.
                Si vous n'avez pas effectué cette demande, ignorez cet email.
            </div>

            <div class="divider"></div>

            <p class="footer-text">
                Cet email a été envoyé automatiquement, merci de ne pas y répondre.<br>
                Pour toute assistance, contactez-nous sur <strong>support@koloimmo.com</strong>
            </p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <small>© {{ date('Y') }} Kolo Immo — Résidences meublées en Afrique de l'Ouest<br>
            Ce message a été envoyé car une action a été effectuée sur votre compte.</small>
        </div>
    </div>
</div>
</body>
</html>
