@extends('layouts.admin')
@section('title', 'Gestion des litiges')
@section('content')

{{-- Stats --}}
<div class="grid grid-cols-3 gap-4 mb-6">
    <div class="bg-red-50 border border-red-100 rounded-xl p-4 text-center">
        <p class="text-2xl font-bold text-red-600">{{ $counts['open'] }}</p>
        <p class="text-xs font-semibold text-red-500 mt-0.5">Ouverts</p>
    </div>
    <div class="bg-yellow-50 border border-yellow-100 rounded-xl p-4 text-center">
        <p class="text-2xl font-bold text-yellow-600">{{ $counts['in_progress'] }}</p>
        <p class="text-xs font-semibold text-yellow-500 mt-0.5">En cours</p>
    </div>
    <div class="bg-green-50 border border-green-100 rounded-xl p-4 text-center">
        <p class="text-2xl font-bold text-green-600">{{ $counts['resolved'] }}</p>
        <p class="text-xs font-semibold text-green-500 mt-0.5">Résolus</p>
    </div>
</div>

{{-- Filters --}}
<div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 mb-5">
    <form action="{{ route('admin.disputes.index') }}" method="GET" class="flex gap-3 items-end flex-wrap">
        <div>
            <label class="block text-xs font-semibold text-gray-500 mb-1">STATUT</label>
            <select name="status" class="px-3 py-2 border border-gray-200 rounded-lg text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Tous</option>
                @foreach(\App\Models\Dispute::STATUSES as $val => $s)
                <option value="{{ $val }}" {{ request('status') === $val ? 'selected' : '' }}>{{ $s['label'] }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="bg-blue-700 text-white font-semibold px-4 py-2 rounded-lg text-sm">Filtrer</button>
        <a href="{{ route('admin.disputes.index') }}" class="border border-gray-200 text-gray-600 font-semibold px-4 py-2 rounded-lg text-sm hover:bg-gray-50">Réinitialiser</a>
    </form>
</div>

{{-- Table --}}
<div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
    <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
        <h2 class="font-bold text-gray-900">Litiges ({{ $disputes->total() }})</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                    <th class="text-left px-5 py-3">ID</th>
                    <th class="text-left px-5 py-3">Motif</th>
                    <th class="text-left px-5 py-3">Plaignant</th>
                    <th class="text-left px-5 py-3">Mis en cause</th>
                    <th class="text-left px-5 py-3 hidden md:table-cell">Réservation</th>
                    <th class="text-center px-5 py-3">Statut</th>
                    <th class="text-left px-5 py-3">Date</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($disputes as $d)
                @php $color = $d->statusColor(); @endphp
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-5 py-3 text-gray-400 text-xs font-mono">#{{ $d->id }}</td>
                    <td class="px-5 py-3"><span class="text-xs font-medium text-gray-700">{{ $d->reasonLabel() }}</span></td>
                    <td class="px-5 py-3">
                        <p class="font-medium text-gray-900">{{ $d->openedBy->name }}</p>
                        <p class="text-xs text-gray-400">{{ ucfirst($d->openedBy->role) }}</p>
                    </td>
                    <td class="px-5 py-3">
                        <p class="font-medium text-gray-900">{{ $d->againstUser->name }}</p>
                        <p class="text-xs text-gray-400">{{ ucfirst($d->againstUser->role) }}</p>
                    </td>
                    <td class="px-5 py-3 hidden md:table-cell">
                        <p class="text-xs font-mono text-gray-600">{{ $d->booking->reference }}</p>
                        <p class="text-xs text-gray-400">{{ Str::limit($d->booking->property->title ?? '—', 25) }}</p>
                    </td>
                    <td class="px-5 py-3 text-center">
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-{{ $color }}-100 text-{{ $color }}-700">
                            <span class="w-1.5 h-1.5 rounded-full bg-{{ $color }}-500"></span>
                            {{ $d->statusLabel() }}
                        </span>
                    </td>
                    <td class="px-5 py-3 text-xs text-gray-500">{{ $d->created_at->format('d/m/Y') }}</td>
                    <td class="px-5 py-3">
                        <a href="{{ route('admin.disputes.show', $d) }}"
                           class="text-blue-600 hover:text-blue-800 text-xs font-medium px-2 py-1 rounded bg-blue-50 hover:bg-blue-100 transition-colors">
                            Examiner
                        </a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="px-5 py-10 text-center text-gray-400">Aucun litige</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($disputes->hasPages())
    <div class="px-5 py-4 border-t border-gray-100">{{ $disputes->withQueryString()->links() }}</div>
    @endif
</div>
@endsection
