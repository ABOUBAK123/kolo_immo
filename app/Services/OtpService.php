<?php

namespace App\Services;

use App\Mail\OtpMail;
use App\Models\OtpCode;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class OtpService
{
    /**
     * Generate and send OTP via configured channels.
     *
     * @param  string       $phone    Numéro de téléphone (peut être null si email seul)
     * @param  string       $purpose  phone_verify | login | password_reset | contract_sign
     * @param  Model|null   $model    Modèle lié (User...)
     * @param  string|null  $email    Adresse email (optionnel)
     * @param  array|null   $channels Canaux forcés. Si null → lit OTP_CHANNELS dans .env
     */
    public function generate(
        string  $phone,
        string  $purpose,
        ?Model  $model = null,
        ?string $email = null,
        ?array  $channels = null
    ): OtpCode {
        // Invalider les OTPs précédents pour ce téléphone/objet
        OtpCode::where('phone', $phone)
            ->where('purpose', $purpose)
            ->where('used', false)
            ->update(['used' => true]);

        $code = $this->generateCode();

        $otp = OtpCode::create([
            'phone'        => $phone,
            'email'        => $email,
            'code'         => $code,
            'purpose'      => $purpose,
            'otpable_type' => $model ? get_class($model) : null,
            'otpable_id'   => $model ? $model->getKey() : null,
            'used'         => false,
            'expires_at'   => now()->addMinutes(10),
        ]);

        $recipientName = ($model instanceof User) ? $model->name : '';
        $message       = $this->buildSmsMessage($purpose, $code);
        $activeChannels = $channels ?? $this->getActiveChannels();

        foreach ($activeChannels as $channel) {
            match ($channel) {
                'sms'       => $phone  ? $this->sendSms($phone, $message) : null,
                'whatsapp'  => $phone  ? $this->sendWhatsApp($phone, $code, $purpose) : null,
                'email'     => $email  ? $this->sendEmail($email, $code, $purpose, $recipientName) : null,
                default     => null,
            };
        }

        return $otp;
    }

    /**
     * Vérifier un code OTP.
     */
    public function verify(string $phone, string $code, string $purpose): bool
    {
        $otp = OtpCode::where('phone', $phone)
            ->where('code', $code)
            ->where('purpose', $purpose)
            ->valid()
            ->latest()
            ->first();

        if (!$otp) {
            return false;
        }

        $otp->markAsUsed();
        return true;
    }

    // ─── Canal SMS — Africa's Talking ─────────────────────────────────────────

    public function sendSms(string $phone, string $message): void
    {
        $username = config('services.africastalking.username');
        $apiKey   = config('services.africastalking.api_key');
        $senderId = config('services.africastalking.sender_id', 'KOLOIMMO');

        if (empty($apiKey) || $apiKey === 'votre_api_key_africastalking') {
            Log::warning('[OTP SMS] Africa\'s Talking non configuré — code affiché en log uniquement.', [
                'to' => $phone, 'message' => $message,
            ]);
            return;
        }

        $baseUrl = $username === 'sandbox'
            ? 'https://api.sandbox.africastalking.com/version1/messaging'
            : 'https://api.africastalking.com/version1/messaging';

        try {
            $response = Http::withHeaders([
                'apiKey' => $apiKey,
                'Accept' => 'application/json',
            ])->asForm()->post($baseUrl, [
                'username' => $username,
                'to'       => $phone,
                'message'  => $message,
                'from'     => $senderId,
            ]);

            if ($response->successful()) {
                Log::info('[OTP SMS] Envoyé via Africa\'s Talking', ['to' => $phone]);
            } else {
                Log::error('[OTP SMS] Échec Africa\'s Talking', [
                    'to'       => $phone,
                    'status'   => $response->status(),
                    'response' => $response->body(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('[OTP SMS] Exception', ['to' => $phone, 'error' => $e->getMessage()]);
        }
    }

    // ─── Canal WhatsApp — Meta Cloud API ──────────────────────────────────────

    public function sendWhatsApp(string $phone, string $code, string $purpose): void
    {
        $token        = config('services.whatsapp.api_token');
        $phoneId      = config('services.whatsapp.phone_number_id');
        $templateName = config('services.whatsapp.template_name', 'otp_code');

        if (empty($token) || $token === 'votre_token_meta_whatsapp') {
            Log::warning('[OTP WhatsApp] Meta WhatsApp non configuré — code ignoré.', [
                'to' => $phone, 'code' => $code,
            ]);
            return;
        }

        // Normaliser le numéro (enlever + et espaces)
        $to = preg_replace('/\D/', '', $phone);

        try {
            $response = Http::withToken($token)
                ->post("https://graph.facebook.com/v19.0/{$phoneId}/messages", [
                    'messaging_product' => 'whatsapp',
                    'to'                => $to,
                    'type'              => 'template',
                    'template'          => [
                        'name'       => $templateName,
                        'language'   => ['code' => 'fr'],
                        'components' => [[
                            'type'       => 'body',
                            'parameters' => [
                                ['type' => 'text', 'text' => $code],
                                ['type' => 'text', 'text' => '10'],
                            ],
                        ]],
                    ],
                ]);

            if ($response->successful()) {
                Log::info('[OTP WhatsApp] Envoyé via Meta', ['to' => $phone]);
            } else {
                Log::error('[OTP WhatsApp] Échec Meta', [
                    'to'       => $phone,
                    'status'   => $response->status(),
                    'response' => $response->json(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('[OTP WhatsApp] Exception', ['to' => $phone, 'error' => $e->getMessage()]);
        }
    }

    // ─── Canal Email ──────────────────────────────────────────────────────────

    public function sendEmail(string $email, string $code, string $purpose, string $name = ''): void
    {
        try {
            Mail::to($email)->queue(new OtpMail($code, $purpose, $name));
            Log::info('[OTP Email] Mis en file d\'attente', ['to' => $email, 'purpose' => $purpose]);
        } catch (\Throwable $e) {
            Log::error('[OTP Email] Échec mise en file', ['to' => $email, 'error' => $e->getMessage()]);
        }
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    protected function generateCode(): string
    {
        return str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
    }

    protected function buildSmsMessage(string $purpose, string $code): string
    {
        return match ($purpose) {
            'phone_verify'   => "KOLO IMMO - Code de vérification : {$code}. Valable 10 min. Ne le partagez pas.",
            'login'          => "KOLO IMMO - Code de connexion : {$code}. Valable 10 min.",
            'contract_sign'  => "KOLO IMMO - Code de signature contrat : {$code}. Valable 10 min.",
            'password_reset' => "KOLO IMMO - Code de réinitialisation : {$code}. Valable 10 min.",
            default          => "KOLO IMMO - Votre code : {$code}. Valable 10 min.",
        };
    }

    protected function getActiveChannels(): array
    {
        $env = env('OTP_CHANNELS', 'sms');
        return array_map('trim', explode(',', $env));
    }
}
