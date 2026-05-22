@extends('layouts.app')

@section('title', 'Connexion - Kolo Immo')

@section('content')
<div class="min-h-screen bg-gray-50 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full">

        <!-- Logo -->
        <div class="text-center mb-8">
            <a href="{{ route('home') }}" class="inline-flex items-center gap-2">
                <div class="w-12 h-12 bg-blue-700 rounded-2xl flex items-center justify-center">
                    <svg class="w-7 h-7 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                    </svg>
                </div>
                <span class="text-2xl font-bold text-blue-700">Kolo <span class="text-orange-400">Immo</span></span>
            </a>
            <h1 class="mt-4 text-2xl font-bold text-gray-900">Bon retour !</h1>
            <p class="text-gray-500 text-sm mt-1">Connectez-vous à votre compte</p>
        </div>

        <!-- Card -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-8">

            <!-- Errors -->
            @if($errors->any())
            <div class="mb-4 bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3">
                <ul class="list-disc list-inside text-sm space-y-1">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form action="{{ route('login') }}" method="POST" class="space-y-5">
                @csrf

                <!-- Email or phone -->
                <div>
                    <label for="login" class="block text-sm font-semibold text-gray-700 mb-1.5">
                        Email ou téléphone
                    </label>
                    <div class="relative">
                        <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        <input type="text" id="login" name="email" value="{{ old('email') }}"
                            placeholder="exemple@email.com"
                            class="w-full pl-10 pr-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm transition-all {{ $errors->has('email') ? 'border-red-300 bg-red-50' : '' }}"
                            autocomplete="username" required>
                    </div>
                </div>

                <!-- Password -->
                <div x-data="{ showPassword: false }">
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="password" class="block text-sm font-semibold text-gray-700">
                            Mot de passe
                        </label>
                        @if(Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                            Mot de passe oublié ?
                        </a>
                        @endif
                    </div>
                    <div class="relative">
                        <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                        <input :type="showPassword ? 'text' : 'password'" id="password" name="password"
                            placeholder="••••••••"
                            class="w-full pl-10 pr-12 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm transition-all {{ $errors->has('password') ? 'border-red-300 bg-red-50' : '' }}"
                            autocomplete="current-password" required>
                        <button type="button" @click="showPassword = !showPassword"
                            class="absolute right-3.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            <svg x-show="!showPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <svg x-show="showPassword" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Remember me -->
                <div class="flex items-center">
                    <input type="checkbox" id="remember" name="remember" {{ old('remember') ? 'checked' : '' }}
                        class="w-4 h-4 rounded border-gray-300 text-blue-700 focus:ring-blue-500">
                    <label for="remember" class="ml-2 text-sm text-gray-600 cursor-pointer">
                        Se souvenir de moi
                    </label>
                </div>

                <!-- Submit -->
                <button type="submit"
                    class="w-full bg-blue-700 hover:bg-blue-800 text-white font-bold py-3.5 rounded-xl transition-all duration-200 shadow-sm hover:shadow-md text-sm">
                    Se connecter
                </button>
            </form>

            <!-- Divider -->
            <div class="relative my-6">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-gray-200"></div>
                </div>
                <div class="relative flex justify-center">
                    <span class="bg-white px-4 text-sm text-gray-400">ou</span>
                </div>
            </div>

            <!-- Register link -->
            <div class="text-center space-y-3">
                @if(Route::has('verify.phone.form'))
                <a href="{{ route('verify.phone.form') }}" class="block w-full border-2 border-gray-200 text-gray-700 font-semibold py-3 rounded-xl text-sm hover:bg-gray-50 hover:border-gray-300 transition-all">
                    <span class="flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                        </svg>
                        Inscription via téléphone
                    </span>
                </a>
                @endif
                <p class="text-sm text-gray-500">
                    Pas encore inscrit ?
                    <a href="{{ route('register') }}" class="font-semibold text-blue-700 hover:text-blue-900 transition-colors">
                        Créer un compte
                    </a>
                </p>
            </div>
        </div>

        <!-- Trust badges -->
        <div class="mt-6 flex items-center justify-center gap-6 text-xs text-gray-400">
            <span class="flex items-center gap-1">
                <svg class="w-3.5 h-3.5 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg>
                Connexion sécurisée
            </span>
            <span class="flex items-center gap-1">
                <svg class="w-3.5 h-3.5 text-blue-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 1.944A11.954 11.954 0 012.166 5C2.056 5.649 2 6.319 2 7c0 5.225 3.34 9.67 8 11.317C14.66 16.67 18 12.225 18 7c0-.682-.057-1.35-.166-2.001A11.954 11.954 0 0110 1.944zM11 14a1 1 0 11-2 0 1 1 0 012 0zm0-7a1 1 0 10-2 0v3a1 1 0 102 0V7z" clip-rule="evenodd"/></svg>
                Données protégées
            </span>
        </div>
    </div>
</div>
@endsection
