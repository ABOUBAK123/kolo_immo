@extends('layouts.app')

@section('title', 'Disponibilités - ' . $property->title)

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('owner.properties.index') }}" class="text-blue-600 hover:underline text-sm">← Mes logements</a>
        <span class="text-gray-400">/</span>
        <span class="text-gray-700 font-medium">Disponibilités</span>
    </div>

    <h1 class="text-2xl font-bold text-gray-900 mb-2">{{ $property->title }}</h1>
    <p class="text-gray-500 text-sm mb-6">Bloquez des dates pour empêcher les réservations (ex: usage personnel, travaux)</p>

    @if(session('success'))
    <div class="mb-4 bg-green-50 border border-green-200 rounded-xl p-4 text-sm text-green-700">{{ session('success') }}</div>
    @endif

    @if($errors->any())
    <div class="mb-4 bg-red-50 border border-red-200 rounded-xl p-4 text-sm text-red-700">
        @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Add blocked dates form -->
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
            <h2 class="font-bold text-gray-900 mb-4">Bloquer une période</h2>
            <form method="POST" action="{{ route('owner.properties.availability.save', $property) }}">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date de début</label>
                        <input type="date" name="start_date" value="{{ old('start_date') }}"
                            min="{{ date('Y-m-d') }}"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date de fin</label>
                        <input type="date" name="end_date" value="{{ old('end_date') }}"
                            min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Motif (optionnel)</label>
                        <input type="text" name="reason" value="{{ old('reason') }}"
                            placeholder="Ex: Usage personnel, travaux..."
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <button type="submit"
                        class="w-full py-2.5 bg-blue-700 text-white rounded-xl font-semibold text-sm hover:bg-blue-800 transition-colors">
                        Bloquer ces dates
                    </button>
                </div>
            </form>
        </div>

        <!-- Existing blocked dates -->
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 font-bold text-gray-900">
                Dates bloquées ({{ $blockedDates->count() }})
            </div>
            @forelse($blockedDates as $date)
            <div class="flex items-center justify-between px-5 py-3 border-b border-gray-50 last:border-0">
                <div>
                    <p class="text-sm font-medium text-gray-900">
                        {{ \Carbon\Carbon::parse($date['start'])->format('d/m/Y') }} → {{ \Carbon\Carbon::parse($date['end'])->format('d/m/Y') }}
                    </p>
                    @if($date['reason'])
                    <p class="text-xs text-gray-400">{{ $date['reason'] }}</p>
                    @endif
                    @if($date['source'] !== 'manual')
                    <span class="text-xs text-blue-500">Via {{ $date['source'] }}</span>
                    @endif
                </div>
                @if($date['source'] === 'manual')
                <form method="POST" action="{{ route('owner.properties.availability.delete', [$property, $date['id']]) }}">
                    @csrf @method('DELETE')
                    <button type="submit" class="text-red-500 hover:text-red-700 text-xs font-medium"
                        onclick="return confirm('Débloquer ces dates ?')">
                        Supprimer
                    </button>
                </form>
                @endif
            </div>
            @empty
            <div class="px-5 py-8 text-center text-gray-400 text-sm">
                Aucune date bloquée manuellement
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
