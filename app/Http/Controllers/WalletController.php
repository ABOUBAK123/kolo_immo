<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WalletController extends Controller
{
    // ─── Dashboard ────────────────────────────────────────────────────────────

    public function index()
    {
        $user   = Auth::user();
        $wallet = $user->getOrCreateWallet();
        $txns   = $wallet->transactions()->with('booking.property')->paginate(15);

        return view('wallet.index', compact('wallet', 'txns'));
    }

    // ─── Recharge via CinetPay ────────────────────────────────────────────────

    public function topup(Request $request)
    {
        $data = $request->validate([
            'amount' => ['required', 'integer', 'min:500', 'max:5000000'],
        ], [
            'amount.min' => 'Le montant minimum est 500 XOF.',
            'amount.max' => 'Le montant maximum est 5 000 000 XOF.',
        ]);

        $user      = Auth::user();
        $wallet    = $user->getOrCreateWallet();
        $reference = 'WLT-' . strtoupper(Str::random(12));

        // Create pending topup transaction
        $txn = $wallet->transactions()->create([
            'type'          => 'topup',
            'amount'        => $data['amount'],
            'balance_after' => $wallet->balance, // will update on webhook
            'description'   => 'Recharge wallet via Mobile Money',
            'reference'     => $reference,
            'status'        => 'pending',
        ]);

        // Initiate CinetPay payment
        $apiKey  = config('services.cinetpay.api_key');
        $siteId  = config('services.cinetpay.site_id');

        try {
            $response = Http::post('https://api-checkout.cinetpay.com/v2/payment', [
                'apikey'           => $apiKey,
                'site_id'          => $siteId,
                'transaction_id'   => $reference,
                'amount'           => $data['amount'],
                'currency'         => 'XOF',
                'description'      => 'Recharge portefeuille Kolo Immo',
                'return_url'       => route('wallet.topup.success'),
                'cancel_url'       => route('wallet.index'),
                'notify_url'       => route('wallet.topup.webhook'),
                'customer_name'    => $user->name,
                'customer_email'   => $user->email ?? '',
                'customer_phone_number' => $user->phone ?? '',
                'channels'         => 'ALL',
                'lang'             => 'fr',
                'metadata'         => json_encode(['wallet_id' => $wallet->id, 'txn_id' => $txn->id]),
            ]);

            if ($response->successful() && isset($response['data']['payment_url'])) {
                return redirect($response['data']['payment_url']);
            }
        } catch (\Throwable $e) {
            Log::error('[Wallet Topup] CinetPay error: ' . $e->getMessage());
        }

        $txn->update(['status' => 'failed']);
        return back()->with('error', 'Impossible d\'initier le paiement. Veuillez réessayer.');
    }

    public function topupSuccess()
    {
        return redirect()->route('wallet.index')
            ->with('success', 'Votre recharge est en cours de traitement. Votre solde sera mis à jour sous quelques minutes.');
    }

    public function topupWebhook(Request $request)
    {
        $reference = $request->input('transaction_id') ?? $request->input('cpm_trans_id');
        $status    = $request->input('payment_status') ?? $request->input('cpm_result');

        if (!$reference) {
            return response()->json(['error' => 'Missing reference'], 400);
        }

        $txn = WalletTransaction::where('reference', $reference)->first();
        if (!$txn) {
            return response()->json(['error' => 'Transaction not found'], 404);
        }

        if ($txn->status === 'completed') {
            return response()->json(['ok' => true]); // idempotent
        }

        if (in_array($status, ['00', 'ACCEPTED'])) {
            DB::transaction(function () use ($txn) {
                $wallet = $txn->wallet;
                $wallet->increment('balance', $txn->amount);
                $wallet->refresh();
                $txn->update(['status' => 'completed', 'balance_after' => $wallet->balance]);
            });
        } else {
            $txn->update(['status' => 'failed']);
        }

        return response()->json(['ok' => true]);
    }

    // ─── Pay booking from wallet ───────────────────────────────────────────────

    public function payBooking(Request $request, Booking $booking)
    {
        $user = Auth::user();

        if ($user->id !== $booking->tenant_id) {
            abort(403);
        }

        if (!in_array($booking->payment_status, ['pending'])) {
            return back()->with('error', 'Cette réservation est déjà payée.');
        }

        $wallet = $user->getOrCreateWallet();

        if (!$wallet->hasSufficientBalance((float) $booking->total_amount)) {
            return back()->with('error',
                'Solde insuffisant. Votre solde : ' . $wallet->formattedBalance() .
                ' — Montant requis : ' . number_format($booking->total_amount, 0, ',', ' ') . ' XOF.'
            );
        }

        DB::transaction(function () use ($wallet, $booking) {
            $wallet->debit(
                (float) $booking->total_amount,
                "Paiement réservation #{$booking->reference} — {$booking->property->title}",
                $booking->id
            );
            $booking->update([
                'payment_status' => 'wallet',
                'status'         => 'confirmed',
                'confirmed_at'   => now(),
            ]);
        });

        return redirect()->route('bookings.show', $booking)
            ->with('success', 'Paiement effectué depuis votre portefeuille. Réservation confirmée !');
    }
}
