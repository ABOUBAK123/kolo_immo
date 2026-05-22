@extends('layouts.app')

@section('title', ($type === 'entry' ? 'État des lieux d\'entrée' : 'État des lieux de sortie') . ' - Kolo Immo')

@section('content')
<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-gray-100">
            <div class="flex items-center gap-3 mb-1">
                <a href="{{ route('contracts.show', $contract) }}" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <h1 class="text-xl font-bold text-gray-900">
                    {{ $type === 'entry' ? 'État des lieux d\'entrée' : 'État des lieux de sortie' }}
                </h1>
            </div>
            <p class="text-sm text-gray-500 ml-8">Contrat #{{ $contract->reference }}</p>
        </div>

        <div class="p-6">
            @if(session('success'))
            <div class="bg-green-50 border border-green-100 text-green-700 rounded-xl p-4 mb-6 text-sm font-medium">
                {{ session('success') }}
            </div>
            @endif

            @if($errors->any())
            <div class="bg-red-50 border border-red-100 text-red-700 rounded-xl p-4 mb-6 text-sm">
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <!-- Current inspection status -->
            @php
                $existingPath = $type === 'entry'
                    ? $contract->entry_inspection_path
                    : $contract->exit_inspection_path;
            @endphp

            @if($existingPath)
            <div class="bg-blue-50 rounded-xl p-4 mb-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-semibold text-blue-700">Document soumis</p>
                        <p class="text-xs text-blue-500 mt-0.5">
                            {{ $type === 'entry' ? $contract->entry_inspection_at?->format('d/m/Y H:i') : $contract->exit_inspection_at?->format('d/m/Y H:i') }}
                        </p>
                    </div>
                    <a href="{{ asset('storage/' . $existingPath) }}" target="_blank"
                       class="text-xs font-semibold text-blue-700 hover:text-blue-900 underline">
                        Voir le document
                    </a>
                </div>
            </div>
            @endif

            <div class="mb-6">
                <h2 class="font-semibold text-gray-900 mb-2">
                    {{ $type === 'entry' ? 'Soumettre l\'état des lieux d\'entrée' : 'Soumettre l\'état des lieux de sortie' }}
                </h2>
                <p class="text-sm text-gray-500">
                    Téléversez le document signé d'état des lieux (PDF ou image). Maximum 10 Mo.
                </p>
            </div>

            <form action="{{ route('contracts.inspection.save', [$contract, $type]) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="border-2 border-dashed border-gray-200 rounded-xl p-8 text-center mb-6" x-data="{ file: null }"
                     @dragover.prevent @drop.prevent="file = $event.dataTransfer.files[0]">
                    <svg class="w-10 h-10 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                    </svg>
                    <p class="text-sm text-gray-500 mb-2">Glissez-déposez ou</p>
                    <label class="cursor-pointer bg-blue-700 text-white text-sm font-semibold px-4 py-2 rounded-lg hover:bg-blue-800 transition-colors">
                        Choisir un fichier
                        <input type="file" name="inspection_file" class="hidden" accept=".pdf,.jpg,.jpeg,.png"
                               @change="file = $event.target.files[0]">
                    </label>
                    <p x-show="file" class="text-xs text-green-700 font-medium mt-3" x-text="'Fichier sélectionné : ' + (file ? file.name : '')"></p>
                    <p class="text-xs text-gray-400 mt-2">PDF, JPG, JPEG, PNG — max 10 Mo</p>
                </div>

                <button type="submit" class="w-full bg-blue-700 text-white font-semibold py-3 rounded-xl hover:bg-blue-800 transition-colors">
                    Soumettre l'état des lieux
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
