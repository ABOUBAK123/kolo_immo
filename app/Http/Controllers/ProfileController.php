<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function show()
    {
        $user = Auth::user()->load('kycDocuments');
        $kycStatus = $user->kycDocuments->last()?->status ?? 'none';
        return view('profile.index', compact('user', 'kycStatus'));
    }

    public function updateInfo(Request $request)
    {
        $user = Auth::user();

        $data = $request->validate([
            'name'     => ['required', 'string', 'max:100'],
            'email'    => ['required', 'email', 'unique:users,email,' . $user->id],
            'phone'    => ['required', 'string', 'max:20', 'unique:users,phone,' . $user->id],
            'city'     => ['nullable', 'string', 'max:100'],
            'country'  => ['nullable', 'string', 'max:100'],
            'language' => ['nullable', 'string', 'max:5'],
            'currency' => ['nullable', 'string', 'max:3', 'in:' . implode(',', array_keys(\App\Helpers\Currency::$currencies))],
        ], [
            'name.required'  => 'Le nom complet est obligatoire.',
            'email.required' => "L'adresse email est obligatoire.",
            'email.unique'   => 'Cette adresse email est déjà utilisée.',
            'phone.required' => 'Le numéro de téléphone est obligatoire.',
            'phone.unique'   => 'Ce numéro de téléphone est déjà utilisé.',
        ]);

        $user->update($data);

        return back()->with('success', 'Vos informations ont été mises à jour.')->with('tab', 'infos');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'string'],
            'password'         => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ], [
            'current_password.required' => 'Le mot de passe actuel est obligatoire.',
            'password.required'         => 'Le nouveau mot de passe est obligatoire.',
            'password.confirmed'        => 'Les mots de passe ne correspondent pas.',
            'password.min'              => 'Le mot de passe doit contenir au moins 8 caractères.',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()
                ->withErrors(['current_password' => 'Le mot de passe actuel est incorrect.'])
                ->with('tab', 'securite');
        }

        $user->update(['password' => Hash::make($request->password)]);

        return back()->with('success', 'Mot de passe modifié avec succès.')->with('tab', 'securite');
    }

    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'avatar.required' => 'Veuillez sélectionner une image.',
            'avatar.image'    => 'Le fichier doit être une image.',
            'avatar.max'      => 'La photo ne doit pas dépasser 2 Mo.',
        ]);

        $user = Auth::user();

        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        $path = $request->file('avatar')->store('avatars', 'public');
        $user->update(['avatar' => $path]);

        return back()->with('success', 'Photo de profil mise à jour.')->with('tab', 'infos');
    }
}
