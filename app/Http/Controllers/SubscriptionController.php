<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubscriptionController extends Controller
{
    /** Page publique des plans */
    public function plans()
    {
        $plans        = Plan::where('is_active', true)->orderBy('sort_order')->get();
        $currentPlan  = Auth::check() ? Auth::user()->currentPlan() : null;

        return view('subscriptions.plans', compact('plans', 'currentPlan'));
    }

    /** Page de gestion de l'abonnement courant */
    public function mySubscription()
    {
        $user         = Auth::user();
        $subscription = $user->activeSubscription();
        $plans        = Plan::where('is_active', true)->orderBy('sort_order')->get();

        return view('subscriptions.my-subscription', compact('subscription', 'plans'));
    }

    /** Initier un abonnement (crée en pending, redirige vers paiement CinetPay) */
    public function subscribe(Request $request)
    {
        $request->validate(['plan_id' => ['required', 'exists:plans,id']]);

        $plan = Plan::findOrFail($request->plan_id);
        $user = Auth::user();

        // Plans gratuits : activation immédiate
        if ($plan->isFree()) {
            $user->subscriptions()->where('status', 'active')->update(['status' => 'cancelled', 'cancelled_at' => now()]);

            Subscription::create([
                'user_id'   => $user->id,
                'plan_id'   => $plan->id,
                'status'    => 'active',
                'starts_at' => now(),
                'ends_at'   => now()->addYear(),
            ]);

            return redirect()->route('subscriptions.my')->with('success', 'Abonnement gratuit activé.');
        }

        // Plans payants : créer en pending et rediriger vers CinetPay
        $sub = Subscription::create([
            'user_id'   => $user->id,
            'plan_id'   => $plan->id,
            'status'    => 'pending',
            'starts_at' => now(),
            'ends_at'   => now()->addMonth(),
        ]);

        // Initier paiement CinetPay
        $ref      = 'SUB-' . strtoupper(uniqid());
        $sub->update(['payment_reference' => $ref]);

        $cinetpayData = [
            'apikey'           => config('services.cinetpay.api_key'),
            'site_id'          => config('services.cinetpay.site_id'),
            'transaction_id'   => $ref,
            'amount'           => $plan->price_monthly,
            'currency'         => 'XOF',
            'description'      => "Abonnement {$plan->name} - Kolo Immo",
            'return_url'       => route('subscriptions.callback', ['sub' => $sub->id, 'status' => 'success']),
            'cancel_url'       => route('subscriptions.callback', ['sub' => $sub->id, 'status' => 'cancel']),
            'notify_url'       => route('subscriptions.notify'),
            'customer_name'    => $user->name,
            'customer_email'   => $user->email ?? '',
            'customer_phone_number' => $user->phone ?? '',
            'customer_address' => $user->city ?? 'Abidjan',
            'customer_city'    => $user->city ?? 'Abidjan',
            'customer_country' => 'CI',
        ];

        $response = \Illuminate\Support\Facades\Http::post('https://api-checkout.cinetpay.com/v2/payment', $cinetpayData);
        $result   = $response->json();

        if (isset($result['data']['payment_url'])) {
            return redirect($result['data']['payment_url']);
        }

        $sub->delete();
        return back()->with('error', 'Impossible d\'initier le paiement. Réessayez.');
    }

    /** Callback CinetPay après paiement */
    public function callback(Request $request, Subscription $sub)
    {
        if ($request->status === 'success') {
            return redirect()->route('subscriptions.my')
                ->with('success', 'Paiement en cours de traitement. Votre abonnement sera activé sous peu.');
        }

        $sub->delete();
        return redirect()->route('subscriptions.plans')
            ->with('error', 'Paiement annulé.');
    }

    /** Webhook CinetPay (notification serveur) */
    public function notify(Request $request)
    {
        $ref = $request->input('cpm_trans_id');
        $sub = Subscription::where('payment_reference', $ref)->first();

        if (!$sub) return response()->json(['message' => 'Not found'], 404);

        // Vérifier le statut auprès de CinetPay
        $check = \Illuminate\Support\Facades\Http::post('https://api-checkout.cinetpay.com/v2/payment/check', [
            'apikey'         => config('services.cinetpay.api_key'),
            'site_id'        => config('services.cinetpay.site_id'),
            'transaction_id' => $ref,
        ])->json();

        if (($check['data']['status'] ?? '') === 'ACCEPTED') {
            // Annuler les abonnements actifs précédents
            $sub->user->subscriptions()
                ->where('id', '!=', $sub->id)
                ->where('status', 'active')
                ->update(['status' => 'cancelled', 'cancelled_at' => now()]);

            $sub->update(['status' => 'active', 'starts_at' => now(), 'ends_at' => now()->addMonth()]);
        } else {
            $sub->update(['status' => 'expired']);
        }

        return response()->json(['message' => 'OK']);
    }

    /** Annuler l'abonnement actif */
    public function cancel()
    {
        $sub = Auth::user()->activeSubscription();

        if ($sub) {
            $sub->update(['status' => 'cancelled', 'cancelled_at' => now()]);
        }

        return redirect()->route('subscriptions.my')
            ->with('success', 'Abonnement annulé. Il reste actif jusqu\'à la fin de la période.');
    }
}
