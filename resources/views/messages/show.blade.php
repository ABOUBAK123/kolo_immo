@extends('layouts.app')

@section('title', 'Conversation - Kolo Immo')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6"
     x-data="{
        newMessage: '',
        sending: false,
        async send() {
            if (!this.newMessage.trim()) return;
            this.sending = true;
            const form = document.getElementById('msg-form');
            const data = new FormData(form);
            const res = await fetch(form.action, { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body: data });
            const json = await res.json();
            if (json.success) {
                this.newMessage = '';
                window.location.reload();
            }
            this.sending = false;
        }
     }">

    <!-- Header -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm mb-4 p-4 flex items-center gap-4">
        <a href="{{ route('messages.index') }}" class="text-gray-400 hover:text-gray-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
        </a>
        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white font-bold flex-shrink-0">
            @php $other = Auth::id() === $conversation->tenant_id ? $conversation->owner : $conversation->tenant; @endphp
            {{ strtoupper(substr($other->name ?? 'U', 0, 1)) }}
        </div>
        <div class="flex-1 min-w-0">
            <p class="font-semibold text-gray-900 truncate">{{ $other->name ?? 'Utilisateur' }}</p>
            <p class="text-xs text-gray-400 truncate">{{ $conversation->property->title ?? 'Propriété' }}</p>
        </div>
        @if($conversation->booking)
        <a href="{{ route('bookings.show', $conversation->booking) }}" class="text-xs text-blue-700 font-semibold hover:text-blue-900 flex-shrink-0">
            Réservation #{{ $conversation->booking->reference }}
        </a>
        @endif
    </div>

    <!-- Messages -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm mb-4 overflow-hidden">
        <div class="p-4 space-y-4 max-h-[60vh] overflow-y-auto" id="messages-container">
            @forelse($conversation->messages as $msg)
            @php $isMine = $msg->sender_id === Auth::id(); @endphp
            <div class="flex {{ $isMine ? 'justify-end' : 'justify-start' }} gap-2">
                @if(!$isMine)
                <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center text-gray-600 font-bold text-xs flex-shrink-0 mt-1">
                    {{ strtoupper(substr($msg->sender->name ?? 'U', 0, 1)) }}
                </div>
                @endif
                <div class="max-w-[70%]">
                    <div class="{{ $isMine ? 'bg-blue-700 text-white' : 'bg-gray-100 text-gray-900' }} rounded-2xl {{ $isMine ? 'rounded-tr-md' : 'rounded-tl-md' }} px-4 py-2.5">
                        @if($msg->body)
                        <p class="text-sm leading-relaxed">{{ $msg->body }}</p>
                        @endif
                        @if($msg->attachment_path)
                        @if($msg->attachment_type === 'image')
                        <img src="{{ asset('storage/' . $msg->attachment_path) }}" alt="Image" class="mt-2 rounded-lg max-w-full max-h-48 object-cover">
                        @else
                        <a href="{{ asset('storage/' . $msg->attachment_path) }}" target="_blank" class="{{ $isMine ? 'text-blue-200' : 'text-blue-700' }} text-xs underline mt-1 block">
                            📎 Pièce jointe
                        </a>
                        @endif
                        @endif
                    </div>
                    <p class="text-xs text-gray-400 mt-1 {{ $isMine ? 'text-right' : 'text-left' }} px-1">
                        {{ $msg->created_at->diffForHumans() }}
                        @if($isMine && $msg->read_at)
                        · Lu
                        @endif
                    </p>
                </div>
            </div>
            @empty
            <div class="text-center py-10 text-gray-400 text-sm">
                Aucun message pour l'instant. Commencez la conversation !
            </div>
            @endforelse
        </div>
    </div>

    <!-- Send form -->
    <form id="msg-form" action="{{ route('messages.send', $conversation) }}" method="POST"
          @submit.prevent="send" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
        @csrf
        <div class="flex gap-3 items-end">
            <div class="flex-1">
                <textarea name="body" x-model="newMessage" rows="2"
                    placeholder="Écrivez un message..."
                    class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm resize-none focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    @keydown.enter.prevent="if(!$event.shiftKey) send()"></textarea>
            </div>
            <div class="flex flex-col gap-2">
                <label class="cursor-pointer text-gray-400 hover:text-gray-600 p-2 rounded-lg hover:bg-gray-50 transition-colors" title="Ajouter une pièce jointe">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                    </svg>
                    <input type="file" name="attachment" class="hidden" accept="image/*,.pdf,.doc,.docx">
                </label>
                <button type="submit" :disabled="sending || !newMessage.trim()"
                    class="bg-blue-700 text-white rounded-xl p-2.5 hover:bg-blue-800 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                    </svg>
                </button>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('messages-container');
    if (container) container.scrollTop = container.scrollHeight;
});
</script>
@endsection
