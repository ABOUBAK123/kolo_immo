<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function __construct(protected OtpService $otpService)
    {
    }

    /**
     * Register a new user (phone + OTP flow).
     */
    public function register(Request $request)
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:100'],
            'email'    => ['nullable', 'email', 'unique:users,email'],
            'phone'    => ['required_without:email', 'nullable', 'string', 'max:20', 'unique:users,phone'],
            'password' => ['required', Password::min(8)->letters()->numbers()],
            'role'     => ['required', 'in:tenant,owner,both,agent'],
            'country'  => ['nullable', 'string', 'max:100'],
            'city'     => ['nullable', 'string', 'max:100'],
        ]);

        $needsAdminActivation = in_array($data['role'], ['owner', 'agent']);

        // Keep only valid 2-letter ISO code for backward compat with VARCHAR(2) column
        $raw = $data['country'] ?? 'CI';
        $country = (preg_match('/^[A-Za-z]{2}$/', $raw)) ? strtoupper($raw) : 'CI';

        try {
            $user = User::create([
                'name'        => $data['name'],
                'email'       => $data['email'] ?? null,
                'phone'       => $data['phone'] ?? null,
                'password'    => Hash::make($data['password']),
                'role'        => $data['role'],
                'country'     => $country,
                'city'        => $data['city'] ?? null,
                'kyc_status'  => 'pending',
                'is_active'   => !$needsAdminActivation,
                'is_banned'   => false,
                'trust_score' => 50,
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            \Illuminate\Support\Facades\Log::error('[Register] DB error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création du compte. Veuillez réessayer.',
            ], 500);
        }

        // Send OTP for phone verification
        if ($user->phone) {
            $this->otpService->generate($user->phone, 'phone_verify', $user, $user->email);
        }

        if ($needsAdminActivation) {
            $message = 'Votre compte a bien été créé et sera activé par l\'administrateur avant que vous puissiez vous connecter.';
        } elseif ($user->phone) {
            $message = 'Compte créé. Veuillez vérifier votre téléphone avec le code OTP envoyé.';
        } else {
            $message = 'Compte créé avec succès.';
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => [
                'user_id'            => $user->id,
                'phone'              => $user->phone,
                'email'              => $user->email,
                'needs_activation'   => $needsAdminActivation,
            ],
        ], 201);
    }

    /**
     * Login with email + password.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email'    => ['nullable', 'email'],
            'phone'    => ['nullable', 'string'],
            'password' => ['required', 'string'],
        ]);

        if (!$request->filled('email') && !$request->filled('phone')) {
            return response()->json([
                'success' => false,
                'message' => 'Email ou numéro de téléphone requis.',
                'errors'  => ['email' => ['Email ou téléphone requis.']],
            ], 422);
        }

        $user = $request->filled('phone')
            ? User::where('phone', $request->phone)->first()
            : User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Identifiants incorrects.',
            ], 401);
        }

        if ($user->is_banned) {
            return response()->json([
                'success' => false,
                'message' => 'Votre compte a été suspendu.',
            ], 403);
        }

        if (!$user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Votre compte est en attente d\'activation par l\'administrateur.',
            ], 403);
        }

        $token = $user->createToken('mobile-app', ['*'])->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Connexion réussie.',
            'data'    => [
                'token'    => $token,
                'user'     => $this->formatUser($user),
            ],
        ]);
    }

    /**
     * Verify OTP for phone verification.
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'phone'   => ['required', 'string'],
            'code'    => ['required', 'string', 'size:6'],
            'purpose' => ['required', 'in:phone_verify,login,password_reset'],
        ]);

        $valid = $this->otpService->verify($request->phone, $request->code, $request->purpose);

        if (!$valid) {
            return response()->json([
                'success' => false,
                'message' => 'Code OTP incorrect ou expiré.',
            ], 422);
        }

        $user = User::where('phone', $request->phone)->first();

        if ($user && $request->purpose === 'phone_verify') {
            $user->update(['phone_verified_at' => now()]);
        }

        // Issue token after OTP verification
        $token = null;
        if ($user) {
            $token = $user->createToken('mobile-app', ['*'])->plainTextToken;
        }

        return response()->json([
            'success' => true,
            'message' => 'Code vérifié avec succès.',
            'data'    => [
                'token' => $token,
                'user'  => $user ? $this->formatUser($user) : null,
            ],
        ]);
    }

    /**
     * Resend OTP to phone number.
     */
    public function resendOtp(Request $request)
    {
        $request->validate([
            'phone'   => ['required', 'string'],
            'purpose' => ['nullable', 'in:phone_verify,login,password_reset'],
        ]);

        $user = User::where('phone', $request->phone)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Numéro de téléphone introuvable.',
            ], 404);
        }

        $purpose = $request->input('purpose', 'phone_verify');
        $this->otpService->generate($user->phone, $purpose, $user, $user->email);

        return response()->json([
            'success' => true,
            'message' => 'Un nouveau code a été envoyé au ' . $user->phone,
        ]);
    }

    /**
     * Step 1 — Send OTP for password reset.
     * POST /api/v1/auth/forgot-password
     */
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'contact' => ['required', 'string'],
        ], [
            'contact.required' => 'Veuillez saisir votre email ou numéro de téléphone.',
        ]);

        $contact = trim($request->contact);

        $user = User::where('phone', $contact)
            ->orWhere('email', $contact)
            ->first();

        // Always return success to avoid user enumeration
        if (!$user) {
            return response()->json([
                'success' => true,
                'message' => 'Si ce contact existe, un code de vérification a été envoyé.',
                'data'    => ['phone' => null],
            ]);
        }

        $phone = $user->phone ?? $contact;
        $this->otpService->generate($phone, 'password_reset', $user, $user->email);

        $masked = $this->maskContact($phone);
        $via    = $user->phone ? 'SMS' : 'email';

        return response()->json([
            'success' => true,
            'message' => "Code de vérification envoyé par {$via} au {$masked}.",
            'data'    => [
                'phone'  => $phone,
                'masked' => $masked,
                'via'    => $via,
            ],
        ]);
    }

    /**
     * Step 2 — Verify OTP and reset password.
     * POST /api/v1/auth/reset-password
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'phone'                 => ['required', 'string'],
            'code'                  => ['required', 'string', 'size:6'],
            'password'              => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ], [
            'code.required'              => 'Le code de vérification est obligatoire.',
            'code.size'                  => 'Le code doit contenir exactement 6 chiffres.',
            'password.required'          => 'Le nouveau mot de passe est obligatoire.',
            'password.confirmed'         => 'Les mots de passe ne correspondent pas.',
        ]);

        $valid = $this->otpService->verify($request->phone, $request->code, 'password_reset');

        if (!$valid) {
            return response()->json([
                'success' => false,
                'message' => 'Code incorrect ou expiré.',
                'errors'  => ['code' => ['Code OTP incorrect ou expiré.']],
            ], 422);
        }

        $user = User::where('phone', $request->phone)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur introuvable.',
            ], 404);
        }

        $user->update(['password' => Hash::make($request->password)]);

        // Revoke all existing tokens for security
        $user->tokens()->delete();

        // Issue a fresh token so the user is immediately logged in
        $token = $user->createToken('mobile-app', ['*'])->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Mot de passe réinitialisé avec succès.',
            'data'    => [
                'token' => $token,
                'user'  => $this->formatUser($user->fresh()),
            ],
        ]);
    }

    // ─── Private helpers ──────────────────────────────────────────────────────

    private function maskContact(string $contact): string
    {
        if (filter_var($contact, FILTER_VALIDATE_EMAIL)) {
            [$local, $domain] = explode('@', $contact);
            $masked = substr($local, 0, 2) . str_repeat('*', max(strlen($local) - 2, 3));
            return $masked . '@' . $domain;
        }

        // Phone: keep first 4 and last 2 chars
        $len = strlen($contact);
        if ($len <= 6) return $contact;
        return substr($contact, 0, 4) . str_repeat('*', $len - 6) . substr($contact, -2);
    }

    /**
     * Logout (revoke current token).
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Déconnexion réussie.',
        ]);
    }

    /**
     * Get authenticated user profile.
     */
    public function me(Request $request)
    {
        $user = $request->user();
        $user->load('kycDocuments');

        return response()->json([
            'success' => true,
            'data'    => $this->formatUser($user),
        ]);
    }

    /**
     * Update user profile.
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'name'    => ['sometimes', 'string', 'max:100'],
            'city'    => ['sometimes', 'string', 'max:100'],
            'country' => ['sometimes', 'string', 'max:100'],
            'avatar'  => ['sometimes', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
        ]);

        if ($request->hasFile('avatar')) {
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Profil mis à jour.',
            'data'    => $this->formatUser($user->fresh()),
        ]);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    protected function formatUser(User $user): array
    {
        return [
            'id'                  => $user->id,
            'name'                => $user->name,
            'email'               => $user->email,
            'phone'               => $user->phone,
            'role'                => $user->role,
            'kyc_status'          => $user->kyc_status,
            'avatar_url'          => $user->avatarUrl(),
            'country'             => $user->country,
            'city'                => $user->city,
            'trust_score'         => $user->trust_score,
            'is_kyc_verified'     => $user->isKycVerified(),
            'phone_verified'      => $user->phone_verified_at !== null,
            'email_verified'      => $user->email_verified_at !== null,
            'created_at'          => $user->created_at?->toIso8601String(),
        ];
    }
}
