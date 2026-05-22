<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class OwnerMiddleware
{
    /**
     * Handle an incoming request.
     * Allows users with role 'owner', 'both', or 'admin'.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login')
                ->with('error', 'Veuillez vous connecter pour accéder à cette page.');
        }

        $user = Auth::user();

        if (!$user->isOwner()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Accès réservé aux propriétaires.',
                ], 403);
            }

            return redirect('/dashboard')
                ->with('error', 'Vous devez avoir un compte propriétaire pour accéder à cette section.');
        }

        return $next($request);
    }
}
