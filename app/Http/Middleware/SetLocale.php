<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    const SUPPORTED = ['fr', 'en', 'dyu', 'wo', 'yo', 'bci'];

    public function handle(Request $request, Closure $next): Response
    {
        $locale = 'fr';

        if (Auth::check() && isset(Auth::user()->language) && in_array(Auth::user()->language, self::SUPPORTED)) {
            $locale = Auth::user()->language;
        } elseif ($request->session()->has('locale') && in_array($request->session()->get('locale'), self::SUPPORTED)) {
            $locale = $request->session()->get('locale');
        }

        App::setLocale($locale);
        return $next($request);
    }
}
