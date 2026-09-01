<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    private array $envKeys = [
        // Général
        'OTP_CHANNELS',
        // SMS
        'AFRICAS_TALKING_USERNAME',
        'AFRICAS_TALKING_API_KEY',
        'AFRICAS_TALKING_SENDER_ID',
        // WhatsApp
        'WHATSAPP_API_TOKEN',
        'WHATSAPP_PHONE_NUMBER_ID',
        'WHATSAPP_TEMPLATE_NAME',
        // Email
        'MAIL_MAILER',
        'MAIL_HOST',
        'MAIL_PORT',
        'MAIL_USERNAME',
        'MAIL_PASSWORD',
        'MAIL_FROM_ADDRESS',
        'MAIL_FROM_NAME',
        // Commissions
        'SERVICE_FEE_PERCENT',
        'PLATFORM_COMMISSION_PERCENT',
        // TVA
        'VAT_ENABLED',
        'VAT_PERCENT',
        // CinetPay
        'CINETPAY_API_KEY',
        'CINETPAY_SITE_ID',
        // FCM
        'FCM_SERVER_KEY',
        // Orange Money
        'ORANGE_MONEY_CLIENT_ID',
        'ORANGE_MONEY_CLIENT_SECRET',
        'ORANGE_MONEY_MERCHANT_KEY',
        // MTN Mobile Money
        'MTN_MOMO_SUBSCRIPTION_KEY',
        'MTN_MOMO_API_USER',
        'MTN_MOMO_API_KEY',
        'MTN_MOMO_TARGET_ENVIRONMENT',
        // Wave
        'WAVE_API_KEY',
        'WAVE_WEBHOOK_SECRET',
        // Moov Money
        'MOOV_MONEY_CLIENT_ID',
        'MOOV_MONEY_CLIENT_SECRET',
        'MOOV_MONEY_MERCHANT_NUMBER',
    ];

    private array $sensitiveKeys = [
        'AFRICAS_TALKING_API_KEY',
        'WHATSAPP_API_TOKEN',
        'MAIL_PASSWORD',
        'CINETPAY_API_KEY',
        'FCM_SERVER_KEY',
        'ORANGE_MONEY_CLIENT_SECRET',
        'ORANGE_MONEY_MERCHANT_KEY',
        'MTN_MOMO_SUBSCRIPTION_KEY',
        'MTN_MOMO_API_KEY',
        'WAVE_API_KEY',
        'WAVE_WEBHOOK_SECRET',
        'MOOV_MONEY_CLIENT_SECRET',
    ];

    private array $numericDefaults = [
        'SERVICE_FEE_PERCENT'         => 3,
        'PLATFORM_COMMISSION_PERCENT' => 8,
        'VAT_PERCENT'                 => 0,
    ];

    public function show(Request $request)
    {
        $config = [];
        foreach ($this->envKeys as $key) {
            $raw = env($key);
            // Pour les clés numériques, utiliser le défaut si la valeur est absente ou vide
            if (array_key_exists($key, $this->numericDefaults)) {
                $config[$key] = ($raw !== null && $raw !== '') ? (float) $raw : $this->numericDefaults[$key];
            } else {
                $config[$key] = $raw ?? '';
            }
        }

        $tab = $request->get('tab', 'general');

        return view('admin.settings.index', compact('config', 'tab'));
    }

    public function update(Request $request, string $section)
    {
        $allowed = match ($section) {
            'general'     => ['OTP_CHANNELS'],
            'sms'         => ['AFRICAS_TALKING_USERNAME', 'AFRICAS_TALKING_API_KEY', 'AFRICAS_TALKING_SENDER_ID'],
            'whatsapp'    => ['WHATSAPP_API_TOKEN', 'WHATSAPP_PHONE_NUMBER_ID', 'WHATSAPP_TEMPLATE_NAME'],
            'email'       => ['MAIL_MAILER', 'MAIL_HOST', 'MAIL_PORT', 'MAIL_USERNAME', 'MAIL_PASSWORD', 'MAIL_FROM_ADDRESS', 'MAIL_FROM_NAME'],
            'commissions' => ['SERVICE_FEE_PERCENT', 'PLATFORM_COMMISSION_PERCENT', 'VAT_ENABLED', 'VAT_PERCENT'],
            'payment_api' => [
                'CINETPAY_API_KEY', 'CINETPAY_SITE_ID', 'FCM_SERVER_KEY',
                'ORANGE_MONEY_CLIENT_ID', 'ORANGE_MONEY_CLIENT_SECRET', 'ORANGE_MONEY_MERCHANT_KEY',
                'MTN_MOMO_SUBSCRIPTION_KEY', 'MTN_MOMO_API_USER', 'MTN_MOMO_API_KEY', 'MTN_MOMO_TARGET_ENVIRONMENT',
                'WAVE_API_KEY', 'WAVE_WEBHOOK_SECRET',
                'MOOV_MONEY_CLIENT_ID', 'MOOV_MONEY_CLIENT_SECRET', 'MOOV_MONEY_MERCHANT_NUMBER',
            ],
            default       => [],
        };

        if (empty($allowed)) {
            return back()->with('error', 'Section invalide.');
        }

        // Build OTP_CHANNELS from checkboxes
        if ($section === 'general') {
            $channels = array_filter([
                $request->boolean('channel_sms')      ? 'sms'      : null,
                $request->boolean('channel_whatsapp') ? 'whatsapp' : null,
                $request->boolean('channel_email')    ? 'email'    : null,
            ]);
            $request->merge(['OTP_CHANNELS' => implode(',', $channels) ?: 'sms']);
        }

        $updates = [];
        foreach ($allowed as $key) {
            $value = $request->input($key);

            // Skip empty sensitive fields (keep existing value)
            if (in_array($key, $this->sensitiveKeys) && empty($value)) {
                continue;
            }

            if ($value !== null) {
                $updates[$key] = $value;
            }
        }

        $this->writeEnv($updates);

        // Clear config cache
        try {
            \Artisan::call('config:clear');
        } catch (\Throwable) {
        }

        // Restart queue workers so they pick up the new config (SMTP, SMS, etc.)
        // instead of keeping the settings loaded when they last booted.
        try {
            \Artisan::call('queue:restart');
        } catch (\Throwable) {
        }

        return redirect()->route('admin.settings.show', ['tab' => $section])
            ->with('success', 'Configuration mise à jour avec succès.');
    }

    public function uploadPaymentLogo(Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'method' => ['required', 'in:orange_money,wave,mtn_momo,moov_money'],
            'logo'   => ['required', 'image', 'mimes:png,jpg,jpeg,webp', 'max:1024'],
        ]);

        $method = $request->input('method');
        $dir    = public_path('payment_logos');

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        // Remove any existing logo for this method
        foreach (glob("{$dir}/{$method}.*") as $old) {
            @unlink($old);
        }

        $ext  = $request->file('logo')->getClientOriginalExtension();
        $request->file('logo')->move($dir, "{$method}.{$ext}");

        return redirect()->route('admin.settings.show', ['tab' => 'payments'])
            ->with('success', "Logo {$method} mis à jour.");
    }

    public function deletePaymentLogo(Request $request): \Illuminate\Http\RedirectResponse
    {
        $method = $request->input('method');
        $dir    = public_path('payment_logos');

        foreach (glob("{$dir}/{$method}.*") as $file) {
            @unlink($file);
        }

        return redirect()->route('admin.settings.show', ['tab' => 'payments'])
            ->with('success', "Logo {$method} supprimé.");
    }

    private function writeEnv(array $updates): void
    {
        $path = base_path('.env');
        $content = file_get_contents($path);

        foreach ($updates as $key => $value) {
            // Quote value if it contains spaces
            $formatted = str_contains($value, ' ') ? "\"{$value}\"" : $value;

            if (preg_match("/^{$key}=.*/m", $content)) {
                $content = preg_replace(
                    "/^{$key}=.*/m",
                    "{$key}={$formatted}",
                    $content
                );
            } else {
                $content .= "\n{$key}={$formatted}";
            }
        }

        file_put_contents($path, $content);
    }
}
