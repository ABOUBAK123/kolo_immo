@extends('layouts.admin')

@section('title', 'Paramètres')

@section('content')
<div class="max-w-4xl mx-auto" x-data="{ tab: '{{ $tab }}' }">

    <div class="mb-6">
        <h2 class="text-xl font-bold text-gray-800">Paramètres des intégrations</h2>
        <p class="text-sm text-gray-500 mt-1">Configurez les API de communication utilisées pour l'envoi des codes OTP.</p>
    </div>

    {{-- Tab bar --}}
    <div class="flex gap-1 bg-gray-100 p-1 rounded-xl mb-6 w-fit">
        @foreach([
            ['id' => 'general',  'label' => 'Général',   'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z'],
            ['id' => 'sms',      'label' => 'SMS',        'icon' => 'M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-3 3v-3z'],
            ['id' => 'whatsapp', 'label' => 'WhatsApp',   'icon' => 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z'],
            ['id' => 'email',    'label' => 'Email',      'icon' => 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],
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

</div>
@endsection
