<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    private array $allowedProviders = ['google', 'facebook', 'github'];

    /**
     * Redirect to the OAuth provider (web flow for mobile WebView).
     */
    public function redirect(string $provider)
    {
        if (!in_array($provider, $this->allowedProviders)) {
            abort(404);
        }

        return Socialite::driver($provider)
            ->stateless()
            ->redirect();
    }

    /**
     * Handle the OAuth callback, create/login user, redirect mobile app with token.
     */
    public function callback(string $provider)
    {
        if (!in_array($provider, $this->allowedProviders)) {
            abort(404);
        }

        try {
            $socialUser = Socialite::driver($provider)->stateless()->user();
        } catch (\Throwable $e) {
            return view('auth.social-callback', [
                'success' => false,
                'error'   => 'Authentification annulée ou expirée.',
            ]);
        }

        // Find or create the user
        $user = User::where('email', $socialUser->getEmail())
            ->orWhere("{$provider}_id", $socialUser->getId())
            ->first();

        if (!$user) {
            // New user — create account
            $user = User::create([
                'name'              => $socialUser->getName() ?? $socialUser->getNickname() ?? 'Utilisateur',
                'email'             => $socialUser->getEmail(),
                "{$provider}_id"    => $socialUser->getId(),
                'avatar'            => $socialUser->getAvatar(),
                'password'          => Hash::make(Str::random(32)),
                'role'              => 'tenant',
                'kyc_status'        => 'pending',
                'is_active'         => true,
                'is_banned'         => false,
                'trust_score'       => 50,
                'email_verified_at' => now(),
            ]);
        } else {
            // Existing user — update social ID if not set
            if (empty($user->{"{$provider}_id"})) {
                $user->update(["{$provider}_id" => $socialUser->getId()]);
            }
        }

        if ($user->is_banned) {
            return view('auth.social-callback', [
                'success' => false,
                'error'   => 'Votre compte a été suspendu.',
            ]);
        }

        // Revoke old mobile tokens and create fresh one
        $user->tokens()->where('name', 'mobile-app')->delete();
        $token = $user->createToken('mobile-app', ['*'])->plainTextToken;

        return view('auth.social-callback', [
            'success' => true,
            'token'   => $token,
            'user'    => [
                'id'             => $user->id,
                'name'           => $user->name,
                'email'          => $user->email,
                'phone'          => $user->phone,
                'role'           => $user->role,
                'kyc_status'     => $user->kyc_status,
                'avatar_url'     => $user->avatarUrl(),
                'trust_score'    => $user->trust_score,
                'phone_verified' => $user->phone_verified_at !== null,
            ],
        ]);
    }
}
