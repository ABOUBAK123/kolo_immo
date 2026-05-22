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
    ];

    private array $sensitiveKeys = [
        'AFRICAS_TALKING_API_KEY',
        'WHATSAPP_API_TOKEN',
        'MAIL_PASSWORD',
    ];

    public function show(Request $request)
    {
        $config = [];
        foreach ($this->envKeys as $key) {
            $config[$key] = env($key, '');
        }

        $tab = $request->get('tab', 'general');

        return view('admin.settings.index', compact('config', 'tab'));
    }

    public function update(Request $request, string $section)
    {
        $allowed = match ($section) {
            'general'   => ['OTP_CHANNELS'],
            'sms'       => ['AFRICAS_TALKING_USERNAME', 'AFRICAS_TALKING_API_KEY', 'AFRICAS_TALKING_SENDER_ID'],
            'whatsapp'  => ['WHATSAPP_API_TOKEN', 'WHATSAPP_PHONE_NUMBER_ID', 'WHATSAPP_TEMPLATE_NAME'],
            'email'     => ['MAIL_MAILER', 'MAIL_HOST', 'MAIL_PORT', 'MAIL_USERNAME', 'MAIL_PASSWORD', 'MAIL_FROM_ADDRESS', 'MAIL_FROM_NAME'],
            default     => [],
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

        return redirect()->route('admin.settings.show', ['tab' => $section])
            ->with('success', 'Configuration mise à jour avec succès.');
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
