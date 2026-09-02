@extends('layouts.admin')

@section('title', 'Modifier le propriétaire')

@section('content')
<div class="max-w-2xl mx-auto">

    <div class="mb-6">
        <a href="{{ route('admin.owners.index') }}" class="text-sm text-blue-600 hover:text-blue-800 font-medium">&larr; Retour aux propriétaires</a>
        <h1 class="text-xl font-bold text-gray-900 mt-2">Modifier le propriétaire</h1>
        <p class="text-sm text-gray-500 mt-1">{{ $owner->name }}</p>
    </div>

    @if($errors->any())
    <div class="mb-6 bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3">
        <ul class="list-disc list-inside text-sm space-y-1">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <form action="{{ route('admin.owners.update', $owner) }}" method="POST" class="space-y-5">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Nom complet <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name', $owner->name) }}" required
                    class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm {{ $errors->has('name') ? 'border-red-300 bg-red-50' : '' }}">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Email <span class="text-red-500">*</span></label>
                    <input type="email" name="email" value="{{ old('email', $owner->email) }}" required
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm {{ $errors->has('email') ? 'border-red-300 bg-red-50' : '' }}">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Téléphone <span class="text-red-500">*</span></label>
                    <input type="tel" name="phone" value="{{ old('phone', $owner->phone) }}" required
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm {{ $errors->has('phone') ? 'border-red-300 bg-red-50' : '' }}">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Pays</label>
                    <select name="country"
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm bg-white {{ $errors->has('country') ? 'border-red-300' : '' }}">
                        <option value="">Sélectionner...</option>
                        @foreach(\App\Helpers\WestAfrica::countries() as $countryName => $capital)
                        <option value="{{ $countryName }}" {{ old('country', $owner->country) === $countryName ? 'selected' : '' }}>{{ $countryName }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Ville</label>
                    <input type="text" name="city" value="{{ old('city', $owner->city) }}"
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm {{ $errors->has('city') ? 'border-red-300 bg-red-50' : '' }}">
                </div>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit"
                    class="bg-blue-700 hover:bg-blue-800 text-white font-semibold px-6 py-2.5 rounded-xl text-sm transition-colors">
                    Enregistrer
                </button>
                <a href="{{ route('admin.owners.index') }}"
                    class="border border-gray-200 text-gray-600 font-semibold px-6 py-2.5 rounded-xl text-sm hover:bg-gray-50 transition-colors">
                    Annuler
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
