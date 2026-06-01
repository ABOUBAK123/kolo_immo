<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ForgotPasswordController extends Controller
{
    public function __construct(protected OtpService $otpService) {}

    // ─── Step 1 : Show form ───────────────────────────────────────────────────

    public function show()
    {
        return view('auth.forgot-password');
    }

    // ─── Step 2 : Send OTP ────────────────────────────────────────────────────

    public function sendOtp(Request $request)
    {
        $request->validate([
            'contact' => ['required', 'string'],
        ], [
            'contact.required' => 'Veuillez saisir votre email ou numéro de téléphone.',
        ]);

        $contact = trim($request->contact);

        // Find user by phone or email
        $user = User::where('phone', $contact)
            ->orWhere('email', $contact)
            ->first();

        if (!$user) {
            return back()->withErrors(['contact' => 'Aucun compte associé à cet email ou numéro.']);
        }

        if (!$user->phone && !$user->email) {
            return back()->withErrors(['contact' => 'Ce compte n\'a pas de moyen de contact valide.']);
        }

        // Generate OTP — use phone if available, otherwise email only
        $phone = $user->phone ?? $contact;
        $this->otpService->generate($phone, 'password_reset', $user, $user->email);

        // Store identifier in session
        $request->session()->put('pwd_reset_phone', $phone);
        $request->session()->put('pwd_reset_user_id', $user->id);

        $via = $user->phone ? 'téléphone' : 'email';
        return redirect()->route('password.verify')
            ->with('success', "Un code de vérification a été envoyé sur votre {$via}.");
    }

    // ─── Step 3 : Show OTP + new password form ────────────────────────────────

    public function showVerify(Request $request)
    {
        if (!$request->session()->has('pwd_reset_phone')) {
            return redirect()->route('password.request')
                ->withErrors(['contact' => 'Session expirée. Recommencez.']);
        }

        $phone = $request->session()->get('pwd_reset_phone');
        return view('auth.reset-password', compact('phone'));
    }

    // ─── Step 4 : Verify OTP and reset password ───────────────────────────────

    public function reset(Request $request)
    {
        if (!$request->session()->has('pwd_reset_phone')) {
            return redirect()->route('password.request');
        }

        $phone = $request->session()->get('pwd_reset_phone');
        $userId = $request->session()->get('pwd_reset_user_id');

        $request->validate([
            'code'     => ['required', 'string', 'size:6'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ], [
            'code.required'      => 'Le code de vérification est obligatoire.',
            'code.size'          => 'Le code doit contenir exactement 6 chiffres.',
            'password.required'  => 'Le nouveau mot de passe est obligatoire.',
            'password.confirmed' => 'Les mots de passe ne correspondent pas.',
        ]);

        $valid = $this->otpService->verify($phone, $request->code, 'password_reset');

        if (!$valid) {
            return back()->withErrors(['code' => 'Code incorrect ou expiré. Veuillez réessayer.']);
        }

        $user = User::find($userId);
        if (!$user) {
            return redirect()->route('password.request')
                ->withErrors(['contact' => 'Utilisateur introuvable.']);
        }

        $user->update(['password' => Hash::make($request->password)]);

        // Clear session
        $request->session()->forget(['pwd_reset_phone', 'pwd_reset_user_id']);

        return redirect()->route('login')
            ->with('success', 'Mot de passe réinitialisé avec succès ! Vous pouvez maintenant vous connecter.');
    }

    // ─── Resend OTP ────────────────────────────────────────────────────────────

    public function resend(Request $request)
    {
        $phone = $request->session()->get('pwd_reset_phone');
        $userId = $request->session()->get('pwd_reset_user_id');

        if (!$phone || !$userId) {
            return redirect()->route('password.request');
        }

        $user = User::find($userId);
        if ($user) {
            $this->otpService->generate($phone, 'password_reset', $user, $user->email);
        }

        return back()->with('success', 'Un nouveau code a été envoyé.');
    }
}
