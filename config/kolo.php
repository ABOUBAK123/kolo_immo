<?php

return [
    // Frais de service prélevés sur le locataire (% du subtotal)
    'service_fee_percent' => (float) env('SERVICE_FEE_PERCENT', 3),

    // Commission plateforme prélevée sur le propriétaire à la libération des fonds (% du subtotal)
    'platform_commission_percent' => (float) env('PLATFORM_COMMISSION_PERCENT', 8),

    // TVA — activée ou non, taux en pourcentage
    'vat_enabled' => (bool) env('VAT_ENABLED', false),
    'vat_percent'  => (float) env('VAT_PERCENT', 0),

    // Devise par défaut (code ISO 4217)
    'default_currency' => env('DEFAULT_CURRENCY', 'XOF'),
];
