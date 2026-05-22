<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Services\CinetPayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function __construct(protected CinetPayService $cinetPayService)
    {
    }

    /**
     * Show the payment form with Mobile Money options.
     */
    public function initiate(Booking $booking)
    {
        if (Auth::id() !== $booking->tenant_id) {
            abort(403);
        }

        if ($booking->payment_status !== 'pending') {
            return redirect()->route('bookings.show', $booking)
                ->with('info', 'Ce paiement a déjà été traité.');
        }

        $booking->load(['property', 'owner']);

        $mobileMethods = [
            'orange_money' => [
                'label' => 'Orange Money',
                'icon'  => 'orange_money',
                'color' => '#FF7900',
            ],
            'wave' => [
                'label' => 'Wave',
                'icon'  => 'wave',
                'color' => '#1AC9E8',
            ],
            'mtn_money' => [
                'label' => 'MTN Money',
                'icon'  => 'mtn',
                'color' => '#FFC300',
            ],
            'moov_money' => [
                'label' => 'Moov Money',
                'icon'  => 'moov',
                'color' => '#009999',
            ],
        ];

        return view('payments.initiate', compact('booking', 'mobileMethods'));
    }

    /**
     * Process the payment via CinetPay.
     */
    public function process(Request $request, Booking $booking)
    {
        if (Auth::id() !== $booking->tenant_id) {
            abort(403);
        }

        $request->validate([
            'method'       => ['required', 'in:orange_money,wave,mtn_money,moov_money,card,bank_transfer'],
            'phone_number' => ['required_unless:method,card', 'nullable', 'string', 'max:20'],
        ], [
            'method.required'       => 'Veuillez choisir un mode de paiement.',
            'phone_number.required' => 'Le numéro de téléphone est requis pour le Mobile Money.',
        ]);

        try {
            $payment = $this->cinetPayService->initiatePayment(
                $booking,
                $request->method,
                $request->phone_number ?? ''
            );

            // In dev mode or if immediate success
            if ($payment->status === 'success') {
                return redirect()->route('payments.success', $booking->id)
                    ->with('success', 'Paiement effectué avec succès !');
            }

            // If CinetPay returned a redirect URL
            if (!empty($payment->payment_url)) {
                return redirect()->away($payment->payment_url);
            }

            // Payment is processing
            return redirect()->route('bookings.show', $booking)
                ->with('info', 'Votre paiement est en cours de traitement. Vous recevrez une confirmation par SMS.');
        } catch (\Exception $e) {
            Log::error('[KOLO IMMO] Payment process error', ['error' => $e->getMessage()]);
            return redirect()->route('payments.failed', $booking->id)
                ->with('error', 'Une erreur est survenue lors du paiement. Veuillez réessayer.');
        }
    }

    /**
     * Handle CinetPay webhook notification.
     */
    public function notify(Request $request)
    {
        Log::info('[KOLO IMMO] CinetPay webhook received', $request->all());

        try {
            $processed = $this->cinetPayService->handleNotification($request->all());
            return response()->json(['status' => $processed ? 'OK' : 'FAILED']);
        } catch (\Exception $e) {
            Log::error('[KOLO IMMO] CinetPay webhook error', ['error' => $e->getMessage()]);
            return response()->json(['status' => 'ERROR'], 500);
        }
    }

    /**
     * Payment success page.
     */
    public function success(int $bookingId)
    {
        $booking = Booking::with(['property', 'payment'])->findOrFail($bookingId);

        if (Auth::id() !== $booking->tenant_id) {
            abort(403);
        }

        return view('payments.success', compact('booking'));
    }

    /**
     * Payment failed page.
     */
    public function failed(int $bookingId)
    {
        $booking = Booking::with(['property'])->findOrFail($bookingId);

        if (Auth::id() !== $booking->tenant_id) {
            abort(403);
        }

        return view('payments.failed', compact('booking'));
    }
}
