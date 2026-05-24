{{-- Reusable property card partial. Variable: $property --}}
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-lg transition-all duration-300 group">
    <!-- Photo -->
    <a href="{{ route('properties.show', $property) }}" class="block relative overflow-hidden">
        @if($property->photos->isNotEmpty())
        <img src="{{ $property->photos->first()->photoUrl() }}"
            alt="{{ $property->title }}"
            class="w-full h-52 object-cover group-hover:scale-105 transition-transform duration-300"
            onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
        <div class="hidden w-full h-52 bg-gradient-to-br from-blue-400 to-indigo-600 items-center justify-center">
            <svg class="w-12 h-12 text-white opacity-50" fill="currentColor" viewBox="0 0 20 20">
                <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
            </svg>
        </div>
        @else
        <div class="w-full h-52 bg-gradient-to-br from-blue-400 to-indigo-600 flex items-center justify-center">
            <svg class="w-12 h-12 text-white opacity-50" fill="currentColor" viewBox="0 0 20 20">
                <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
            </svg>
        </div>
        @endif

        <!-- Price badge -->
        <div class="absolute top-3 left-3">
            <span class="bg-white text-gray-900 text-sm font-bold px-3 py-1 rounded-full shadow-md">
                {{ number_format($property->price_per_night, 0, ',', ' ') }} FCFA<span class="text-gray-500 font-normal text-xs">/nuit</span>
            </span>
        </div>

        <!-- Type badge -->
        <div class="absolute top-3 right-3">
            <span class="bg-blue-700 text-white text-xs font-semibold px-2.5 py-1 rounded-full capitalize">
                {{ $property->type ?? 'Appartement' }}
            </span>
        </div>
    </a>

    <!-- Card body -->
    <div class="p-4">
        <!-- Location -->
        <div class="flex items-center gap-1 text-gray-400 text-xs mb-1">
            <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <span>{{ $property->district ? $property->district . ', ' : '' }}{{ $property->city }}</span>
        </div>

        <!-- Title -->
        <a href="{{ route('properties.show', $property) }}">
            <h3 class="font-semibold text-gray-900 text-base leading-snug hover:text-blue-700 transition-colors line-clamp-2 mb-2">
                {{ $property->title }}
            </h3>
        </a>

        <!-- Rating -->
        <div class="flex items-center gap-1.5 mb-3">
            <div class="flex items-center gap-0.5">
                @php $rating = round($property->average_rating ?? 0); @endphp
                @for($i = 1; $i <= 5; $i++)
                <svg class="w-3.5 h-3.5 {{ $i <= $rating ? 'text-yellow-400' : 'text-gray-200' }}" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                </svg>
                @endfor
            </div>
            <span class="text-gray-500 text-xs">
                {{ number_format($property->average_rating ?? 0, 1) }}
                @if($property->reviews_count ?? 0 > 0)
                ({{ $property->reviews_count }} avis)
                @else
                (nouveau)
                @endif
            </span>
        </div>

        <!-- Amenities icons -->
        @if($property->amenities && $property->amenities->isNotEmpty())
        <div class="flex items-center gap-2 mb-3 flex-wrap">
            @foreach($property->amenities->take(4) as $amenity)
            <span class="flex items-center gap-1 text-gray-500 text-xs bg-gray-50 px-2 py-1 rounded-full">
                @if($amenity->amenity === 'wifi')
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"/></svg>
                @elseif($amenity->amenity === 'climatisation')
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17H3a2 2 0 01-2-2V5a2 2 0 012-2h16a2 2 0 012 2v10a2 2 0 01-2 2h-2"/></svg>
                @elseif($amenity->amenity === 'parking')
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h4a3 3 0 010 6H8V7zm0 0V3m0 16v2m0-6h8"/></svg>
                @elseif($amenity->amenity === 'piscine')
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                @else
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                @endif
                {{ ucfirst($amenity->amenity) }}
            </span>
            @endforeach
        </div>
        @endif

        <!-- Footer -->
        <div class="flex items-center justify-between pt-3 border-t border-gray-100">
            <div class="flex items-center gap-1 text-gray-400 text-xs">
                @if($property->owner && $property->owner->kyc_verified)
                <span class="flex items-center gap-1 text-green-600 text-xs font-medium">
                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    Vérifié
                </span>
                @endif
            </div>
            <a href="{{ route('properties.show', $property) }}"
                class="bg-blue-700 hover:bg-blue-800 text-white text-xs font-semibold px-4 py-2 rounded-lg transition-colors">
                Voir
            </a>
        </div>
    </div>
</div>
