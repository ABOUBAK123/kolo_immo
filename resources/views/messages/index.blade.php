@extends('layouts.app')

@section('title', 'Mes messages - Kolo Immo')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6" x-data="{ activeConv: null }">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden" style="height: calc(100vh - 160px); min-height: 500px;">
        <div class="flex h-full">

            <!-- ── Liste des conversations ──────────────────────────────── -->
            <div class="w-full md:w-80 lg:w-96 border-r border-gray-100 flex flex-col flex-shrink-0"
                :class="activeConv ? 'hidden md:flex' : 'flex'">

                <!-- Header -->
                <div class="px-4 py-4 border-b border-gray-100">
                    <h1 class="text-lg font-bold text-gray-900">Messages</h1>
                    <div class="relative mt-3">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input type="text" placeholder="Rechercher une conversation..."
                            class="w-full pl-9 pr-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <!-- Conversation items -->
                <div class="flex-1 overflow-y-auto divide-y divide-gray-50">
                    @forelse($conversations ?? [] as $conv)
                    @php
                        $other   = Auth::id() === $conv->tenant_id ? $conv->owner : $conv->tenant;
                        $lastMsg = $conv->lastMessage;
                        $unread  = $conv->unread_count ?? 0;
                    @endphp
                    <button type="button"
                        @click="activeConv = {{ $conv->id }}"
                        class="w-full text-left px-4 py-4 hover:bg-gray-50 transition-colors flex items-start gap-3"
                        :class="activeConv === {{ $conv->id }} ? 'bg-blue-50 border-l-4 border-blue-700' : ''">

                        <div class="w-11 h-11 rounded-full flex items-center justify-center text-white font-bold flex-shrink-0 text-sm" style="background: linear-gradient(135deg, #1B4F72, #3498DB);">
                            {{ strtoupper(substr($other->name ?? 'U', 0, 1)) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between">
                                <p class="font-semibold text-gray-900 text-sm truncate">{{ $other->name ?? 'Utilisateur' }}</p>
                                <p class="text-xs text-gray-400 flex-shrink-0 ml-2">
                                    {{ $lastMsg ? $lastMsg->created_at->diffForHumans(null, true) : '' }}
                                </p>
                            </div>
                            @if($conv->property)
                            <p class="text-xs text-blue-700 font-medium truncate">{{ $conv->property->title }}</p>
                            @endif
                            <p class="text-xs text-gray-500 truncate mt-0.5">
                                {{ $lastMsg ? Str::limit($lastMsg->body, 40) : 'Aucun message' }}
                            </p>
                        </div>
                        @if($unread > 0)
                        <span class="w-5 h-5 bg-blue-700 text-white text-xs font-bold rounded-full flex items-center justify-center flex-shrink-0">{{ $unread }}</span>
                        @endif
                    </button>
                    @empty
                    <div class="flex flex-col items-center justify-center h-48 px-6 text-center">
                        <svg class="w-12 h-12 text-gray-200 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                        </svg>
                        <p class="text-gray-400 text-sm">Aucune conversation</p>
                        <p class="text-gray-300 text-xs mt-1">Réservez un logement pour contacter un propriétaire</p>
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- ── Zone de messagerie ───────────────────────────────────── -->
            <div class="flex-1 flex flex-col" :class="activeConv ? 'flex' : 'hidden md:flex'">

                <!-- État vide -->
                <div x-show="!activeConv" class="flex-1 flex flex-col items-center justify-center text-center p-8">
                    <div class="w-24 h-24 bg-blue-50 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-12 h-12 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-700 mb-2">Vos messages</h3>
                    <p class="text-gray-400 text-sm">Sélectionnez une conversation pour commencer</p>
                </div>

                <!-- Conversations actives -->
                @foreach($conversations ?? [] as $conv)
                @php $other = Auth::id() === $conv->tenant_id ? $conv->owner : $conv->tenant; @endphp
                <div x-show="activeConv === {{ $conv->id }}" x-cloak class="flex flex-col h-full">

                    <!-- Header de la conversation -->
                    <div class="flex items-center gap-3 px-5 py-4 border-b border-gray-100 bg-white">
                        <button @click="activeConv = null" class="md:hidden text-gray-400 hover:text-gray-600 mr-1">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                        </button>
                        <div class="w-10 h-10 rounded-full flex items-center justify-center text-white font-bold text-sm flex-shrink-0" style="background: linear-gradient(135deg, #1B4F72, #3498DB);">
                            {{ strtoupper(substr($other->name ?? 'U', 0, 1)) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-bold text-gray-900">{{ $other->name ?? 'Utilisateur' }}</p>
                            @if($conv->property)
                            <p class="text-xs text-blue-700 truncate">{{ $conv->property->title }}</p>
                            @endif
                        </div>
                        @if($conv->booking)
                        <a href="{{ route('bookings.show', $conv->booking) }}"
                            class="text-xs font-semibold text-blue-700 bg-blue-50 px-3 py-1.5 rounded-lg hover:bg-blue-100 transition-colors flex-shrink-0">
                            Voir réservation
                        </a>
                        @endif
                    </div>

                    <!-- Messages -->
                    <div class="flex-1 overflow-y-auto p-5 space-y-4 bg-gray-50" id="messages-{{ $conv->id }}">
                        @foreach($conv->messages as $message)
                        @php $isMine = $message->sender_id === Auth::id(); @endphp
                        <div class="flex {{ $isMine ? 'justify-end' : 'justify-start' }} items-end gap-2">
                            @if(!$isMine)
                            <div class="w-7 h-7 rounded-full flex items-center justify-center text-white text-xs font-bold flex-shrink-0" style="background: linear-gradient(135deg, #1B4F72, #3498DB);">
                                {{ strtoupper(substr($other->name ?? 'U', 0, 1)) }}
                            </div>
                            @endif
                            <div class="max-w-xs lg:max-w-md">
                                <div class="px-4 py-2.5 rounded-2xl text-sm leading-relaxed
                                    {{ $isMine ? 'bg-blue-700 text-white rounded-br-sm' : 'bg-white text-gray-800 shadow-sm border border-gray-100 rounded-bl-sm' }}">
                                    {{ $message->body }}
                                </div>
                                @if($message->attachment_path)
                                <div class="mt-1">
                                    @if($message->attachment_type === 'image')
                                    <img src="{{ asset('storage/' . $message->attachment_path) }}" class="max-w-xs rounded-xl border border-gray-200" alt="Image">
                                    @else
                                    <a href="{{ asset('storage/' . $message->attachment_path) }}" target="_blank"
                                        class="flex items-center gap-2 text-xs text-blue-700 bg-blue-50 px-3 py-2 rounded-lg hover:bg-blue-100">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                        </svg>
                                        Pièce jointe
                                    </a>
                                    @endif
                                </div>
                                @endif
                                <p class="text-xs text-gray-400 mt-1 {{ $isMine ? 'text-right' : 'text-left' }}">
                                    {{ $message->created_at->format('H:i') }}
                                    @if($isMine && $message->read_at)
                                    <span class="text-blue-400">✓✓</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <!-- Zone de saisie -->
                    <div class="px-4 py-3 bg-white border-t border-gray-100">
                        <form action="{{ route('messages.send', $conv) }}" method="POST"
                              id="message-form-{{ $conv->id }}"
                              enctype="multipart/form-data"
                              class="flex items-end gap-3">
                            @csrf
                            <div class="flex-1">
                                <textarea name="body" rows="1"
                                    placeholder="Écrivez votre message..."
                                    class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none max-h-32"
                                    @keydown.enter.prevent="if(!$event.shiftKey) $event.target.closest('form').requestSubmit()"></textarea>
                            </div>
                            <!-- Pièce jointe -->
                            <label class="w-10 h-10 flex items-center justify-center text-gray-400 hover:text-gray-600 rounded-xl hover:bg-gray-100 transition-colors cursor-pointer">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                </svg>
                                <input type="file" name="attachment" class="hidden"
                                    accept="image/*,.pdf,.doc,.docx"
                                    onchange="this.closest('form').requestSubmit()">
                            </label>
                            <!-- Envoyer -->
                            <button type="submit"
                                class="w-10 h-10 bg-blue-700 hover:bg-blue-800 text-white rounded-xl flex items-center justify-center transition-colors flex-shrink-0">
                                <svg class="w-4 h-4 rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
