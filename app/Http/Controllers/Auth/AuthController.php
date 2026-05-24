<?php

namespace App\Http\Controllers\Auth;

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

    // ─── Login ────────────────────────────────────────────────────────────────

    public function showLogin()
    {
        if (Auth::check()) {
            return redirect($this->redirectAfterLogin(Auth::user()));
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required'    => 'L\'adresse email est obligatoire.',
            'email.email'       => 'L\'adresse email n\'est pas valide.',
            'password.required' => 'Le mot de passe est obligatoire.',
        ]);

        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Identifiants incorrects. Veuillez réessayer.']);
        }

        $user = Auth::user();

        if ($user->is_banned) {
            Auth::logout();
            return back()->withErrors(['email' => 'Votre compte a été suspendu. Contactez le support.']);
        }

        if (!$user->is_active) {
            Auth::logout();
            return back()->withErrors(['email' => 'Votre compte est en attente d\'activation par l\'administrateur.']);
        }

        $request->session()->regenerate();

        return redirect($this->redirectAfterLogin($user))
            ->with('success', 'Bienvenue, ' . $user->name . ' !');
    }

    // ─── Register ─────────────────────────────────────────────────────────────

    public function showRegister()
    {
        if (Auth::check()) {
            return redirect('/dashboard');
        }
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:100'],
            'email'    => ['required', 'email', 'unique:users,email'],
            'phone'    => ['required', 'string', 'max:20', 'unique:users,phone'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
            'role'     => ['required', 'in:tenant,owner,both,agent'],
            'country'  => ['nullable', 'string', 'max:100'],
            'city'     => ['nullable', 'string', 'max:100'],
        ], [
            'name.required'      => 'Le nom complet est obligatoire.',
            'email.required'     => 'L\'adresse email est obligatoire.',
            'email.unique'       => 'Cette adresse email est déjà utilisée.',
            'phone.required'     => 'Le numéro de téléphone est obligatoire.',
            'phone.unique'       => 'Ce numéro de téléphone est déjà utilisé.',
            'password.required'  => 'Le mot de passe est obligatoire.',
            'password.confirmed' => 'Les mots de passe ne correspondent pas.',
            'role.required'      => 'Veuillez choisir votre profil.',
            'role.in'            => 'Profil invalide.',
        ]);

        $user = User::create([
            'name'       => $data['name'],
            'email'      => $data['email'],
            'phone'      => $data['phone'],
            'password'   => Hash::make($data['password']),
            'role'       => $data['role'],
            'country'    => $data['country'] ?? null,
            'city'       => $data['city'] ?? null,
            'kyc_status' => 'pending',
            'is_active'  => !in_array($data['role'], ['owner', 'agent']),
            'is_banned'  => false,
            'trust_score'=> 50,
        ]);

        // Send phone verification OTP
        $this->otpService->generate($user->phone, 'phone_verify', $user);

        Auth::login($user);
        $request->session()->regenerate();

        $regMessage = in_array($data['role'], ['owner', 'agent'])
            ? 'Votre compte a bien été créé et sera activé par l\'administrateur.'
            : 'Votre compte a bien été créé. Bienvenue sur Kolo Immo !';

        return redirect()->route('verify.phone')
            ->with('reg_message', $regMessage)
            ->with('reg_role', $data['role']);
    }

    // ─── Logout ───────────────────────────────────────────────────────────────

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('success', 'Vous avez été déconnecté avec succès.');
    }

    // ─── Phone Verification ───────────────────────────────────────────────────

    public function showPhoneVerify()
    {
        $user = Auth::user();

        if ($user->phone_verified_at) {
            return redirect($this->redirectAfterLogin($user));
        }

        return view('auth.verify-phone', ['phone' => $user->phone]);
    }

    public function verifyPhone(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ], [
            'code.required' => 'Le code de vérification est obligatoire.',
            'code.size'     => 'Le code doit contenir exactement 6 chiffres.',
        ]);

        $user = Auth::user();

        $valid = $this->otpService->verify($user->phone, $request->code, 'phone_verify');

        if (!$valid) {
            return back()->withErrors(['code' => 'Code incorrect ou expiré. Veuillez réessayer.']);
        }

        $user->update(['phone_verified_at' => now()]);

        return redirect($this->redirectAfterLogin($user))
            ->with('success', 'Téléphone vérifié avec succès !');
    }

    public function resendOtp(Request $request)
    {
        $user = Auth::user();
        $this->otpService->generate($user->phone, 'phone_verify', $user);

        return back()->with('success', 'Un nouveau code a été envoyé au ' . $user->phone);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    protected function redirectAfterLogin(User $user): string
    {
        if ($user->isAdmin()) {
            return '/admin/dashboard';
        }

        if ($user->isOwner() || $user->role === 'agent') {
            return '/owner/dashboard';
        }

        return '/dashboard';
    }
}
