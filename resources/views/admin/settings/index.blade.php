@extends('layouts.admin')

@section('title', 'Paramètres')

@section('content')
<div class="max-w-4xl mx-auto" x-data="{ tab: '{{ $tab }}' }">

    <div class="mb-6">
        <h2 class="text-xl font-bold text-gray-800">Paramètres des intégrations</h2>
        <p class="text-sm text-gray-500 mt-1">Configurez les API de communication utilisées pour l'envoi des codes OTP.</p>
    </div>

    {{-- Tab bar --}}
    <div class="flex gap-1 bg-gray-100 p-1 rounded-xl mb-6 w-fit flex-wrap">
        @foreach([
            ['id' => 'general',  'label' => 'Général',          'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z'],
            ['id' => 'sms',      'label' => 'SMS',              'icon' => 'M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-3 3v-3z'],
            ['id' => 'whatsapp', 'label' => 'WhatsApp',         'icon' => 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z'],
            ['id' => 'email',    'label' => 'Email',            'icon' => 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],
            ['id' => 'payments', 'label' => 'Paiements mobiles','icon' => 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z'],
        ] as $t)
        <button type="button"
            @click="tab = '{{ $t['id'] }}'"
            :class="tab === '{{ $t['id'] }}' ? 'bg-white shadow text-blue-700 font-semibold' : 'text-gray-500 hover:text-gray-700'"
            class="flex items-center gap-2 px-4 py-2 rounded-lg text-sm transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $t['icon'] }}"/>
            </svg>
            {{ $t['label'] }}
        </button>
        @endforeach
    </div>

    {{-- ── Général ──────────────────────────────────────────────────────── --}}
    <div x-show="tab === 'general'" x-cloak>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
                <div class="w-9 h-9 bg-blue-50 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-800">Canaux OTP actifs</h3>
                    <p class="text-xs text-gray-400">Sélectionnez par quels canaux les codes OTP seront envoyés.</p>
                </div>
            </div>
            <form action="{{ route('admin.settings.update', 'general') }}" method="POST" class="px-6 py-6 space-y-4">
                @csrf @method('PUT')

                @php
                    $channels = explode(',', $config['OTP_CHANNELS'] ?? 'sms');
                    $channels = array_map('trim', $channels);
                @endphp

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    @foreach([
                        ['key' => 'sms',      'label' => 'SMS',      'desc' => 'Africa\'s Talking', 'color' => 'green'],
                        ['key' => 'whatsapp', 'label' => 'WhatsApp', 'desc' => 'Meta Cloud API',    'color' => 'emerald'],
                        ['key' => 'email',    'label' => 'Email',    'desc' => 'SMTP (Gmail…)',      'color' => 'blue'],
                    ] as $ch)
                    <label class="relative flex items-start gap-3 p-4 border-2 rounded-xl cursor-pointer transition-colors
                        {{ in_array($ch['key'], $channels) ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-gray-300' }}">
                        <input type="checkbox" name="channel_{{ $ch['key'] }}"
                            value="1" {{ in_array($ch['key'], $channels) ? 'checked' : '' }}
                            class="mt-0.5 h-4 w-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500">
                        <div>
                            <p class="font-semibold text-sm text-gray-800">{{ $ch['label'] }}</p>
                            <p class="text-xs text-gray-400">{{ $ch['desc'] }}</p>
                        </div>
                    </label>
                    @endforeach
                </div>

                <div class="pt-2">
                    <button type="submit"
                        class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-5 py-2.5 rounded-xl transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ── SMS ──────────────────────────────────────────────────────────── --}}
    <div x-show="tab === 'sms'" x-cloak>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
                <div class="w-9 h-9 bg-green-50 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-3 3v-3z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-800">Africa's Talking — SMS</h3>
                    <p class="text-xs text-gray-400">API SMS pour l'Afrique de l'Ouest. <a href="https://africastalking.com" target="_blank" class="text-blue-500 hover:underline">africastalking.com</a></p>
                </div>
            </div>
            <form action="{{ route('admin.settings.update', 'sms') }}" method="POST" class="px-6 py-6 space-y-5">
                @csrf @method('PUT')

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                        <input type="text" name="AFRICAS_TALKING_USERNAME"
                            value="{{ $config['AFRICAS_TALKING_USERNAME'] }}"
                            placeholder="sandbox"
                            class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                        <p class="text-xs text-gray-400 mt-1">Utilisez <code>sandbox</code> pour les tests.</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Sender ID</label>
                        <input type="text" name="AFRICAS_TALKING_SENDER_ID"
                            value="{{ $config['AFRICAS_TALKING_SENDER_ID'] }}"
                            placeholder="KOLOIMMO"
                            class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        API Key
                        @if(!empty($config['AFRICAS_TALKING_API_KEY']) && $config['AFRICAS_TALKING_API_KEY'] !== 'votre_api_key_africastalking')
                        <span class="ml-2 inline-flex items-center gap-1 text-xs font-medium text-green-700 bg-green-50 px-2 py-0.5 rounded-full">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            Configurée
                        </span>
                        @endif
                    </label>
                    <input type="password" name="AFRICAS_TALKING_API_KEY"
                        placeholder="{{ (!empty($config['AFRICAS_TALKING_API_KEY']) && $config['AFRICAS_TALKING_API_KEY'] !== 'votre_api_key_africastalking') ? '••••••••••••••••' : 'Entrez votre clé API' }}"
                        class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                    <p class="text-xs text-gray-400 mt-1">Laissez vide pour conserver la clé actuelle.</p>
                </div>

                <div class="bg-green-50 border border-green-200 rounded-xl p-4 text-sm text-green-800">
                    <p class="font-semibold mb-1">Guide de configuration</p>
                    <p class="text-xs leading-relaxed">Créez un compte sur <strong>africastalking.com</strong>, puis récupérez votre API Key dans le tableau de bord. En production, changez le username de <code>sandbox</code> vers votre username réel.</p>
                </div>

                <div>
                    <button type="submit"
                        class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold px-5 py-2.5 rounded-xl transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Enregistrer la configuration SMS
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ── WhatsApp ─────────────────────────────────────────────────────── --}}
    <div x-show="tab === 'whatsapp'" x-cloak>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
                <div class="w-9 h-9 bg-emerald-50 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-800">Meta WhatsApp Cloud API</h3>
                    <p class="text-xs text-gray-400">Envoi via templates WhatsApp Business. <a href="https://developers.facebook.com" target="_blank" class="text-blue-500 hover:underline">developers.facebook.com</a></p>
                </div>
            </div>
            <form action="{{ route('admin.settings.update', 'whatsapp') }}" method="POST" class="px-6 py-6 space-y-5">
                @csrf @method('PUT')

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Token d'accès permanent
                        @if(!empty($config['WHATSAPP_API_TOKEN']) && $config['WHATSAPP_API_TOKEN'] !== 'votre_token_meta_whatsapp')
                        <span class="ml-2 inline-flex items-center gap-1 text-xs font-medium text-green-700 bg-green-50 px-2 py-0.5 rounded-full">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            Configuré
                        </span>
                        @endif
                    </label>
                    <input type="password" name="WHATSAPP_API_TOKEN"
                        placeholder="{{ (!empty($config['WHATSAPP_API_TOKEN']) && $config['WHATSAPP_API_TOKEN'] !== 'votre_token_meta_whatsapp') ? '••••••••••••••••' : 'Entrez votre token Meta' }}"
                        class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                    <p class="text-xs text-gray-400 mt-1">Laissez vide pour conserver le token actuel.</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number ID</label>
                        <input type="text" name="WHATSAPP_PHONE_NUMBER_ID"
                            value="{{ $config['WHATSAPP_PHONE_NUMBER_ID'] }}"
                            placeholder="1234567890"
                            class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                        <p class="text-xs text-gray-400 mt-1">ID du numéro dans Meta Business.</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nom du template</label>
                        <input type="text" name="WHATSAPP_TEMPLATE_NAME"
                            value="{{ $config['WHATSAPP_TEMPLATE_NAME'] }}"
                            placeholder="otp_code"
                            class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                        <p class="text-xs text-gray-400 mt-1">Template approuvé dans Meta Business Manager.</p>
                    </div>
                </div>

                <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 text-sm text-amber-800">
                    <p class="font-semibold mb-1">Template WhatsApp requis</p>
                    <p class="text-xs leading-relaxed">Créez le template <strong>otp_code</strong> dans Meta Business Manager avec ce corps :<br>
                    <code class="bg-amber-100 px-1 py-0.5 rounded text-xs">Votre code Kolo Immo est &#123;&#123;1&#125;&#125;. Valable &#123;&#123;2&#125;&#125; minutes.</code></p>
                </div>

                <div>
                    <button type="submit"
                        class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold px-5 py-2.5 rounded-xl transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Enregistrer la configuration WhatsApp
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ── Email ────────────────────────────────────────────────────────── --}}
    <div x-show="tab === 'email'" x-cloak>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
                <div class="w-9 h-9 bg-blue-50 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-800">Configuration SMTP — Email</h3>
                    <p class="text-xs text-gray-400">Compatible Gmail, Outlook, Mailgun, etc.</p>
                </div>
            </div>
            <form action="{{ route('admin.settings.update', 'email') }}" method="POST" class="px-6 py-6 space-y-5">
                @csrf @method('PUT')

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Serveur SMTP (Host)</label>
                        <input type="text" name="MAIL_HOST"
                            value="{{ $config['MAIL_HOST'] }}"
                            placeholder="smtp.gmail.com"
                            class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Port</label>
                        <input type="number" name="MAIL_PORT"
                            value="{{ $config['MAIL_PORT'] }}"
                            placeholder="587"
                            class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Adresse email (login)</label>
                        <input type="email" name="MAIL_USERNAME"
                            value="{{ $config['MAIL_USERNAME'] !== 'votre@gmail.com' ? $config['MAIL_USERNAME'] : '' }}"
                            placeholder="votre@gmail.com"
                            class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Mot de passe / App password
                            @if(!empty($config['MAIL_PASSWORD']) && $config['MAIL_PASSWORD'] !== 'votre_mot_de_passe_application')
                            <span class="ml-2 inline-flex items-center gap-1 text-xs font-medium text-green-700 bg-green-50 px-2 py-0.5 rounded-full">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                Configuré
                            </span>
                            @endif
                        </label>
                        <input type="password" name="MAIL_PASSWORD"
                            placeholder="{{ (!empty($config['MAIL_PASSWORD']) && $config['MAIL_PASSWORD'] !== 'votre_mot_de_passe_application') ? '••••••••••••' : 'App password Gmail' }}"
                            class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                        <p class="text-xs text-gray-400 mt-1">Pour Gmail, générez un <strong>mot de passe d'application</strong> (pas votre mot de passe Gmail). Laissez vide pour conserver l'actuel.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Adresse d'expédition (From)</label>
                        <input type="email" name="MAIL_FROM_ADDRESS"
                            value="{{ $config['MAIL_FROM_ADDRESS'] }}"
                            placeholder="noreply@koloimmo.com"
                            class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nom d'expéditeur</label>
                        <input type="text" name="MAIL_FROM_NAME"
                            value="{{ $config['MAIL_FROM_NAME'] }}"
                            placeholder="Kolo Immo"
                            class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                    </div>
                </div>

                <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 text-sm text-blue-800">
                    <p class="font-semibold mb-1">Gmail — Mot de passe d'application</p>
                    <p class="text-xs leading-relaxed">
                        Activez la validation en 2 étapes sur votre compte Google, puis allez dans
                        <strong>Compte Google → Sécurité → Mots de passe des applications</strong>.
                        Créez un mot de passe pour "Autre (nom personnalisé)" → utilisez-le ici.
                    </p>
                </div>

                <div>
                    <button type="submit"
                        class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-5 py-2.5 rounded-xl transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Enregistrer la configuration Email
                    </button>
                </div>
            </form>
        </div>
    </div>


    {{-- ── Paiements mobiles ────────────────────────────────────────────── --}}
    <div x-show="tab === 'payments'" x-cloak>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
                <div class="w-9 h-9 bg-orange-50 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-800">Logos des paiements mobiles</h3>
                    <p class="text-xs text-gray-400">Ces logos s'affichent dans l'application mobile lors du paiement.</p>
                </div>
            </div>
            <div class="px-6 py-6">

                @if(session('success') && request('tab') === 'payments')
                <div class="mb-4 bg-green-50 border border-green-200 text-green-800 text-sm rounded-xl px-4 py-3">
                    {{ session('success') }}
                </div>
                @endif

                @php
                    $paymentMethods = [
                        ['key' => 'orange_money', 'label' => 'Orange Money', 'color' => 'orange'],
                        ['key' => 'wave',         'label' => 'Wave',         'color' => 'blue'],
                        ['key' => 'mtn_momo',     'label' => 'MTN MoMo',     'color' => 'yellow'],
                        ['key' => 'moov_money',   'label' => 'Moov Money',   'color' => 'sky'],
                    ];
                    $logoDir = storage_path('app/public/payment_logos');
                @endphp

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    @foreach($paymentMethods as $pm)
                    @php
                        $existingFiles = is_dir($logoDir) ? glob("{$logoDir}/{$pm['key']}.*") : [];
                        $hasLogo = !empty($existingFiles);
                        $logoUrl = $hasLogo ? asset('storage/payment_logos/' . basename($existingFiles[0])) : null;
                    @endphp
                    <div class="border border-gray-200 rounded-xl p-4 flex flex-col gap-3">
                        <div class="flex items-center justify-between">
                            <p class="font-semibold text-sm text-gray-800">{{ $pm['label'] }}</p>
                            @if($hasLogo)
                            <span class="inline-flex items-center gap-1 text-xs font-medium text-green-700 bg-green-50 px-2 py-0.5 rounded-full">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                Logo configuré
                            </span>
                            @else
                            <span class="text-xs text-gray-400">Aucun logo</span>
                            @endif
                        </div>

                        @if($hasLogo)
                        <div class="flex items-center gap-3">
                            <img src="{{ $logoUrl }}" alt="{{ $pm['label'] }}"
                                class="h-12 w-auto object-contain border border-gray-100 rounded-lg p-1 bg-gray-50">
                        </div>
                        @endif

                        <form action="{{ route('admin.settings.payment-logos.upload') }}" method="POST"
                            enctype="multipart/form-data" class="flex flex-col gap-2">
                            @csrf
                            <input type="hidden" name="method" value="{{ $pm['key'] }}">
                            <label class="block text-xs font-medium text-gray-600">
                                {{ $hasLogo ? 'Remplacer le logo' : 'Uploader un logo' }}
                            </label>
                            <input type="file" name="logo" accept="image/png,image/jpeg,image/webp"
                                class="block w-full text-xs text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer">
                            <p class="text-xs text-gray-400">PNG, JPG ou WebP · max 1 Mo · fond transparent recommandé</p>
                            <button type="submit"
                                class="self-start inline-flex items-center gap-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold px-4 py-2 rounded-lg transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                </svg>
                                Enregistrer
                            </button>
                        </form>

                        @if($hasLogo)
                        <form action="{{ route('admin.settings.payment-logos.delete') }}" method="POST"
                            onsubmit="return confirm('Supprimer le logo {{ $pm['label'] }} ?')">
                            @csrf @method('DELETE')
                            <input type="hidden" name="method" value="{{ $pm['key'] }}">
                            <button type="submit"
                                class="text-xs text-red-500 hover:text-red-700 font-medium">
                                Supprimer le logo
                            </button>
                        </form>
                        @endif
                    </div>
                    @endforeach
                </div>

                <div class="mt-6 bg-blue-50 border border-blue-200 rounded-xl p-4 text-sm text-blue-800">
                    <p class="font-semibold mb-1">Comment ça marche ?</p>
                    <p class="text-xs leading-relaxed">
                        Les logos uploadés ici s'affichent automatiquement dans l'application mobile au moment du paiement.
                        Utilisez des images PNG avec fond transparent (format recommandé : 200×80 px).
                        L'API mobile est disponible à <code class="bg-blue-100 px-1 rounded">{{ url('/api/v1/payment-logos') }}</code>.
                    </p>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
