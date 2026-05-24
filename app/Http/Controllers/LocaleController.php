<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LocaleController extends Controller
{
    const SUPPORTED = ['fr', 'en', 'dyu', 'wo', 'yo', 'bci'];

    const LANGUAGES = [
        'fr'  => ['name' => 'Français', 'flag' => '🇫🇷', 'native' => 'Français'],
        'en'  => ['name' => 'English',  'flag' => '🇬🇧', 'native' => 'English'],
        'dyu' => ['name' => 'Dioula',   'flag' => '🇨🇮', 'native' => 'Julakan'],
        'wo'  => ['name' => 'Wolof',    'flag' => '🇸🇳', 'native' => 'Wolof'],
        'yo'  => ['name' => 'Yoruba',   'flag' => '🇳🇬', 'native' => 'Yorùbá'],
        'bci' => ['name' => 'Baoulé',   'flag' => '🇨🇮', 'native' => 'Baoulé'],
    ];

    public function change(Request $request, string $locale)
    {
        if (!in_array($locale, self::SUPPORTED)) {
            return back();
        }

        $request->session()->put('locale', $locale);

        if (Auth::check()) {
            Auth::user()->update(['language' => $locale]);
        }

        return back();
    }
}
