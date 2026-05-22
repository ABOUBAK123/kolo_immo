<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CinetPayService
{
    protected string $apiKey;
    protected string $siteId;
    protected string $baseUrl;
    protected bool   $devMode;

    public function __construct()
    {
        $this->apiKey  = env('CINETPAY_API_KEY', '');
        $this->siteId  = env('CINETPAY_SITE_ID', '');
        $this->baseUrl = env('CINETPAY_URL', 'https://api-checkout.cinetpay.com/v2');
        $this->devMode = empty($this->apiKey) || app()->environment('local', 'testing');
    }

    /**
     * Initiate a payment for a booking.
     */
    public function initiatePayment(Booking $booking, string $method, string $phoneNumber): Payment
    {
        $transactionId = 'KOLO-' . strtoupper(Str::random(16));

        $payment = Payment::create([
            'transaction_id' => $transactionId,
            'booking_id'     => $booking->id,
            'user_id'        => $booking->tenant_id,
            'amount'         => $booking->total_amount,
            'currency'       => 'XOF',
            'method'         => $method,
            'phone_number'   => $phoneNumber,
            'status'         => 'pending',
            'type'           => 'payment',
        ]);

        if ($this->devMode) {
            return $this->simulatePayment($payment, $booking, $method, $phoneNumber);
        }

        try {
            $payload = [
                'apikey'           => $this->apiKey,
                'site_id'          => $this->siteId,
                'transaction_id'   => $transactionId,
                'amount'           => (int) $booking->total_amount,
                'currency'         => 'XOF',
                'alternative_currency' => '',
                'description'      => 'Réservation KOLO IMMO #' . $booking->reference,
                'customer_id'      => $booking->tenant_id,
                'customer_name'    => $booking->tenant->name,
                'customer_email'   => $booking->tenant->email ?? '',
                'customer_phone_number' => $phoneNumber,
                'customer_address' => $booking->tenant->city ?? '',
                'customer_city'    => $booking->tenant->city ?? '',
                'customer_country' => 'CI',
                'customer_state'   => 'CI',
                'customer_zip_code'=> '00225',
                'notify_url'       => route('payments.notify'),
                'return_url'       => route('payments.success', $booking->id),
                'channels'         => $this->channelFromMethod($method),
                'metadata'         => json_encode(['booking_id' => $booking->id]),
                'lang'             => 'FR',
                'invoice_data'     => json_encode([]),
            ];

            $response = Http::timeout(30)
                ->post($this->baseUrl . '/payment', $payload)
                ->json();

            if (isset($response['code']) && $response['code'] === '201') {
                $payment->update([
                    'status'                  => 'processing',
                    'cinetpay_transaction_id' => $response['data']['transaction_id'] ?? null,
                    'gateway_response'        => $response,
                ]);

                // Store the payment URL in the response for redirect
                $payment->payment_url = $response['data']['payment_url'] ?? null;
            } else {
                $payment->update([
                    'status'           => 'failed',
                    'failure_reason'   => $response['message'] ?? 'Erreur CinetPay',
                    'gateway_response' => $response,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('[KOLO IMMO] CinetPay error', ['error' => $e->getMessage()]);
            $payment->update([
                'status'         => 'failed',
                'failure_reason' => $e->getMessage(),
            ]);
        }

        return $payment->fresh();
    }

    /**
     * Handle the CinetPay payment notification webhook.
     */
    public function handleNotification(array $data): bool
    {
        $transactionId = $data['cpm_trans_id'] ?? $data['transaction_id'] ?? null;

        if (!$transactionId) {
            Log::warning('[KOLO IMMO] CinetPay notification: missing transaction_id');
            return false;
        }

        $payment = Payment::where('transaction_id', $transactionId)
            ->orWhere('cinetpay_transaction_id', $transactionId)
            ->first();

        if (!$payment) {
            Log::warning('[KOLO IMMO] CinetPay notification: payment not found', ['tx' => $transactionId]);
            return false;
        }

        // Verify the payment with CinetPay API
        $verified = $this->verifyTransaction($transactionId);
        $status   = $verified['payment_status'] ?? 'REFUSED';

        if (in_array($status, ['ACCEPTED', 'SUCCESS'])) {
            $payment->update([
                'status'   => 'success',
                'paid_at'  => now(),
                'gateway_response' => $verified,
            ]);

            // Update booking payment status to escrowed
            $payment->booking->update(['payment_status' => 'escrowed']);

            Log::info('[KOLO IMMO] Payment successful', [
                'booking'   => $payment->booking->reference,
                'amount'    => $payment->amount,
            ]);

            return true;
        }

        $payment->update([
            'status'         => 'failed',
            'failure_reason' => $data['cpm_error_message'] ?? 'Paiement refusé',
            'gateway_response' => $verified,
        ]);

        return false;
    }

    /**
     * Process a refund for a payment.
     */
    public function processRefund(Payment $payment): Payment
    {
        if ($payment->status !== 'success') {
            throw new \RuntimeException('Seuls les paiements réussis peuvent être remboursés.');
        }

        $refundTransactionId = 'REFUND-' . strtoupper(Str::random(16));

        if ($this->devMode) {
            $refund = Payment::create([
                'transaction_id' => $refundTransactionId,
                'booking_id'     => $payment->booking_id,
                'user_id'        => $payment->user_id,
                'amount'         => $payment->amount,
                'currency'       => 'XOF',
                'method'         => $payment->method,
                'phone_number'   => $payment->phone_number,
                'status'         => 'success',
                'type'           => 'refund',
                'paid_at'        => now(),
            ]);

            $payment->update(['status' => 'refunded']);
            $payment->booking->update(['payment_status' => 'refunded']);

            Log::info('[KOLO IMMO DEV] Refund simulated', ['amount' => $payment->amount]);
            return $refund;
        }

        try {
            $response = Http::timeout(30)->post($this->baseUrl . '/payment/refund', [
                'apikey'         => $this->apiKey,
                'site_id'        => $this->siteId,
                'transaction_id' => $payment->cinetpay_transaction_id ?? $payment->transaction_id,
            ])->json();

            if (isset($response['code']) && $response['code'] === '0000') {
                $refund = Payment::create([
                    'transaction_id' => $refundTransactionId,
                    'booking_id'     => $payment->booking_id,
                    'user_id'        => $payment->user_id,
                    'amount'         => $payment->amount,
                    'currency'       => 'XOF',
                    'method'         => $payment->method,
                    'phone_number'   => $payment->phone_number,
                    'status'         => 'success',
                    'type'           => 'refund',
                    'gateway_response' => $response,
                    'paid_at'        => now(),
                ]);

                $payment->update(['status' => 'refunded']);
                $payment->booking->update(['payment_status' => 'refunded']);

                return $refund;
            }

            throw new \RuntimeException($response['message'] ?? 'Erreur lors du remboursement');
        } catch (\Exception $e) {
            Log::error('[KOLO IMMO] CinetPay refund error', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Verify a transaction status with CinetPay.
     */
    protected function verifyTransaction(string $transactionId): array
    {
        if ($this->devMode) {
            return ['payment_status' => 'ACCEPTED'];
        }

        try {
            $response = Http::timeout(30)->post($this->baseUrl . '/payment/check', [
                'apikey'         => $this->apiKey,
                'site_id'        => $this->siteId,
                'transaction_id' => $transactionId,
            ])->json();

            return $response['data'] ?? [];
        } catch (\Exception $e) {
            Log::error('[KOLO IMMO] CinetPay verify error', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Simulate a successful payment in development mode.
     */
    protected function simulatePayment(Payment $payment, Booking $booking, string $method, string $phone): Payment
    {
        Log::info('[KOLO IMMO DEV] Payment simulated (dev mode)', [
            'booking'  => $booking->reference,
            'method'   => $method,
            'phone'    => $phone,
            'amount'   => $payment->amount,
        ]);

        $payment->update([
            'status'    => 'success',
            'paid_at'   => now(),
            'gateway_response' => ['simulated' => true, 'dev_mode' => true],
        ]);

        $booking->update(['payment_status' => 'escrowed']);

        return $payment->fresh();
    }

    /**
     * Map payment method to CinetPay channel code.
     */
    protected function channelFromMethod(string $method): string
    {
        return match($method) {
            'orange_money' => 'ORANGE_MONEY',
            'wave'         => 'WAVE',
            'mtn_money'    => 'MTN',
            'moov_money'   => 'MOOV',
            'card'         => 'CREDIT_CARD',
            default        => 'ALL',
        };
    }
}
