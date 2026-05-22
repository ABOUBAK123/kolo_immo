<?php

namespace App\Services;

use App\Models\OtpCode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class OtpService
{
    /**
     * Generate a new OTP code.
     */
    public function generate(
        string $phone,
        string $purpose,
        ?Model $model = null,
        ?string $email = null
    ): OtpCode {
        // Invalidate previous OTPs for same phone/purpose
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

        // Send the SMS
        $message = $this->buildMessage($purpose, $code);
        $this->sendSms($phone, $message);

        return $otp;
    }

    /**
     * Verify an OTP code.
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

    /**
     * Send an SMS message.
     * Africa's Talking / other SMS gateway integration goes here.
     * Currently logs the message for development.
     */
    public function sendSms(string $phone, string $message): void
    {
        // Log SMS for development
        Log::channel('stack')->info('[KOLO IMMO SMS]', [
            'to'      => $phone,
            'message' => $message,
        ]);

        // TODO: Integrate Africa's Talking or another SMS provider
        // Example Africa's Talking integration:
        // $AT = new AfricasTalking\SDK\AfricasTalking(
        //     env('AT_USERNAME'),
        //     env('AT_API_KEY')
        // );
        // $sms = $AT->sms();
        // $sms->send([
        //     'to'      => $phone,
        //     'message' => $message,
        //     'from'    => env('AT_SENDER_ID', 'KOLOIMMO'),
        // ]);
    }

    /**
     * Generate a random 6-digit code.
     */
    protected function generateCode(): string
    {
        return str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Build the SMS message based on purpose.
     */
    protected function buildMessage(string $purpose, string $code): string
    {
        return match($purpose) {
            'phone_verify'    => "KOLO IMMO - Votre code de vérification est : {$code}. Valable 10 minutes.",
            'login'           => "KOLO IMMO - Votre code de connexion est : {$code}. Valable 10 minutes.",
            'contract_sign'   => "KOLO IMMO - Votre code de signature de contrat est : {$code}. Valable 10 minutes.",
            'password_reset'  => "KOLO IMMO - Votre code de réinitialisation de mot de passe est : {$code}. Valable 10 minutes.",
            default           => "KOLO IMMO - Votre code est : {$code}. Valable 10 minutes.",
        };
    }
}
