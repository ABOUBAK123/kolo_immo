<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class KycVerifiedMiddleware
{
    /**
     * Handle an incoming request.
     * Ensures the user has a verified KYC status before proceeding.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login')
                ->with('error', 'Veuillez vous connecter pour accéder à cette page.');
        }

        $user = Auth::user();

        // Admins bypass KYC check
        if ($user->isAdmin()) {
            return $next($request);
        }

        if (!$user->isKycVerified()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Votre identité doit être vérifiée pour effectuer cette action. Complétez votre KYC.',
                    'kyc_required' => true,
                ], 403);
            }

            return redirect()->route('profile.kyc')
                ->with('warning', 'Veuillez vérifier votre identité (KYC) avant d\'effectuer une réservation.');
        }

        return $next($request);
    }
}
