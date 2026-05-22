<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\KycDocument;
use App\Models\Property;
use App\Models\PropertyAmenity;
use App\Models\PropertyBlockedDate;
use App\Models\PropertyPhoto;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // ─── ADMIN ────────────────────────────────────────────────────────────
        $admin = User::create([
            'name'               => 'Administrateur KOLO',
            'email'              => 'admin@koloimmo.com',
            'phone'              => '+22500000001',
            'password'           => Hash::make('Admin@2026'),
            'role'               => 'admin',
            'kyc_status'         => 'verified',
            'country'            => 'CI',
            'city'               => 'Abidjan',
            'trust_score'        => 100,
            'is_active'          => true,
            'is_banned'          => false,
            'email_verified_at'  => now(),
            'phone_verified_at'  => now(),
        ]);

        // ─── OWNERS ───────────────────────────────────────────────────────────
        $owners = [
            [
                'name'    => 'Kofi Mensah',
                'email'   => 'kofi.mensah@demo.com',
                'phone'   => '+22500000010',
                'country' => 'CI',
                'city'    => 'Abidjan',
            ],
            [
                'name'    => 'Aminata Diallo',
                'email'   => 'aminata.diallo@demo.com',
                'phone'   => '+22100000020',
                'country' => 'SN',
                'city'    => 'Dakar',
            ],
            [
                'name'    => 'Ibrahim Coulibaly',
                'email'   => 'ibrahim.coulibaly@demo.com',
                'phone'   => '+22600000030',
                'country' => 'ML',
                'city'    => 'Bamako',
            ],
        ];

        $ownerModels = [];
        foreach ($owners as $ownerData) {
            $owner = User::create([
                'name'              => $ownerData['name'],
                'email'             => $ownerData['email'],
                'phone'             => $ownerData['phone'],
                'password'          => Hash::make('Owner@2026'),
                'role'              => 'owner',
                'kyc_status'        => 'verified',
                'country'           => $ownerData['country'],
                'city'              => $ownerData['city'],
                'trust_score'       => rand(70, 95),
                'is_active'         => true,
                'is_banned'         => false,
                'email_verified_at' => now(),
                'phone_verified_at' => now(),
            ]);

            KycDocument::create([
                'user_id'       => $owner->id,
                'type'          => 'cni',
                'document_path' => 'kyc/demo/document_placeholder.jpg',
                'selfie_path'   => 'kyc/demo/selfie_placeholder.jpg',
                'status'        => 'approved',
                'verified_at'   => now(),
                'verified_by'   => $admin->id,
            ]);

            $ownerModels[] = $owner;
        }

        // ─── TENANTS ──────────────────────────────────────────────────────────
        $tenants = [
            ['name' => 'Aïcha Traoré',    'email' => 'aicha.traore@demo.com',    'phone' => '+22500000100', 'country' => 'CI', 'city' => 'Abidjan'],
            ['name' => 'Moussa Bamba',     'email' => 'moussa.bamba@demo.com',     'phone' => '+22500000101', 'country' => 'CI', 'city' => 'Bouaké'],
            ['name' => 'Fatou Ndiaye',     'email' => 'fatou.ndiaye@demo.com',     'phone' => '+22100000102', 'country' => 'SN',        'city' => 'Dakar'],
            ['name' => 'Seydou Ouédraogo', 'email' => 'seydou.ouedraogo@demo.com', 'phone' => '+22600000103', 'country' => 'BF',   'city' => 'Ouagadougou'],
            ['name' => 'Mariama Bah',      'email' => 'mariama.bah@demo.com',      'phone' => '+22400000104', 'country' => 'GN',         'city' => 'Conakry'],
        ];

        $tenantModels = [];
        foreach ($tenants as $tenantData) {
            $tenant = User::create([
                'name'              => $tenantData['name'],
                'email'             => $tenantData['email'],
                'phone'             => $tenantData['phone'],
                'password'          => Hash::make('Tenant@2026'),
                'role'              => 'tenant',
                'kyc_status'        => 'verified',
                'country'           => $tenantData['country'],
                'city'              => $tenantData['city'],
                'trust_score'       => rand(60, 85),
                'is_active'         => true,
                'is_banned'         => false,
                'email_verified_at' => now(),
                'phone_verified_at' => now(),
            ]);
            $tenantModels[] = $tenant;
        }

        // ─── PROPERTIES ───────────────────────────────────────────────────────
        $propertiesData = [
            [
                'owner_id'          => $ownerModels[0]->id,
                'title'             => 'Studio moderne Cocody',
                'description'       => 'Beau studio entièrement meublé dans le quartier résidentiel de Cocody. Idéal pour une personne ou un couple. Climatisation, Wi-Fi haut débit, cuisine équipée. À proximité des commerces et transports.',
                'type'              => 'studio',
                'country'           => 'CI',
                'city'              => 'Abidjan',
                'district'          => 'Cocody',
                'address'           => 'Rue des Jardins, Cocody, Abidjan',
                'capacity'          => 2,
                'bedrooms'          => 1,
                'bathrooms'         => 1,
                'area_sqm'          => 35,
                'price_per_night'   => 15000,
                'price_per_week'    => 90000,
                'price_per_month'   => 300000,
                'deposit_amount'    => 30000,
                'booking_type'      => 'instant',
                'cancellation_policy' => 'flexible',
                'allow_pets'        => false,
                'allow_smoking'     => false,
                'allow_parties'     => false,
                'check_in_time'     => '14:00',
                'check_out_time'    => '12:00',
                'status'            => 'active',
                'rating_avg'        => 4.7,
                'rating_count'      => 12,
                'views_count'       => 340,
                'is_featured'       => true,
                'amenities'         => ['wifi', 'air_conditioning', 'equipped_kitchen', 'hot_water', 'tv', 'security'],
            ],
            [
                'owner_id'          => $ownerModels[0]->id,
                'title'             => 'Appartement 2 chambres Plateau',
                'description'       => 'Magnifique appartement au cœur du quartier d\'affaires du Plateau. Vue sur la lagune, parking sécurisé, gardien 24h. Parfait pour les voyages d\'affaires ou les familles.',
                'type'              => 'apartment',
                'country'           => 'CI',
                'city'              => 'Abidjan',
                'district'          => 'Plateau',
                'address'           => 'Boulevard de la République, Plateau, Abidjan',
                'capacity'          => 4,
                'bedrooms'          => 2,
                'bathrooms'         => 2,
                'area_sqm'          => 80,
                'price_per_night'   => 35000,
                'price_per_week'    => 200000,
                'price_per_month'   => 700000,
                'deposit_amount'    => 70000,
                'booking_type'      => 'request',
                'cancellation_policy'=> 'moderate',
                'allow_pets'        => false,
                'allow_smoking'     => false,
                'allow_parties'     => false,
                'check_in_time'     => '15:00',
                'check_out_time'    => '11:00',
                'status'            => 'active',
                'rating_avg'        => 4.9,
                'rating_count'      => 8,
                'views_count'       => 520,
                'is_featured'       => true,
                'amenities'         => ['wifi', 'air_conditioning', 'parking', 'security', 'elevator', 'equipped_kitchen', 'tv', 'hot_water'],
            ],
            [
                'owner_id'          => $ownerModels[1]->id,
                'title'             => 'Villa avec piscine Almadies',
                'description'       => 'Somptueuse villa 4 chambres avec piscine privée dans le quartier huppé des Almadies. Jardin tropical, barbecue, salle de sport. Location idéale pour familles et groupes.',
                'type'              => 'villa',
                'country'           => 'SN',
                'city'              => 'Dakar',
                'district'          => 'Almadies',
                'address'           => 'Route des Almadies, Dakar',
                'capacity'          => 10,
                'bedrooms'          => 4,
                'bathrooms'         => 3,
                'area_sqm'          => 300,
                'price_per_night'   => 120000,
                'price_per_week'    => 750000,
                'price_per_month'   => 2500000,
                'deposit_amount'    => 240000,
                'booking_type'      => 'request',
                'cancellation_policy'=> 'strict',
                'allow_pets'        => true,
                'allow_smoking'     => false,
                'allow_parties'     => true,
                'check_in_time'     => '16:00',
                'check_out_time'    => '12:00',
                'status'            => 'active',
                'rating_avg'        => 5.0,
                'rating_count'      => 5,
                'views_count'       => 890,
                'is_featured'       => true,
                'amenities'         => ['wifi', 'pool', 'gym', 'parking', 'garden', 'security', 'air_conditioning', 'equipped_kitchen', 'tv', 'washer', 'iron'],
            ],
            [
                'owner_id'          => $ownerModels[1]->id,
                'title'             => 'Chambre meublée Ouakam',
                'description'       => 'Chambre confortable dans une résidence sécurisée à Ouakam. Cuisine partagée, connexion Wi-Fi, accès à la terrasse. Idéale pour étudiant ou voyageur seul.',
                'type'              => 'room',
                'country'           => 'SN',
                'city'              => 'Dakar',
                'district'          => 'Ouakam',
                'address'           => 'Cité des Eaux, Ouakam, Dakar',
                'capacity'          => 1,
                'bedrooms'          => 1,
                'bathrooms'         => 1,
                'area_sqm'          => 18,
                'price_per_night'   => 8000,
                'price_per_week'    => 50000,
                'price_per_month'   => 150000,
                'deposit_amount'    => 16000,
                'booking_type'      => 'instant',
                'cancellation_policy'=> 'flexible',
                'allow_pets'        => false,
                'allow_smoking'     => false,
                'allow_parties'     => false,
                'check_in_time'     => '14:00',
                'check_out_time'    => '12:00',
                'status'            => 'active',
                'rating_avg'        => 4.4,
                'rating_count'      => 20,
                'views_count'       => 210,
                'is_featured'       => false,
                'amenities'         => ['wifi', 'hot_water', 'security'],
            ],
            [
                'owner_id'          => $ownerModels[2]->id,
                'title'             => 'Duplex standing ACI 2000',
                'description'       => 'Duplex moderne et spacieux dans le quartier ACI 2000 de Bamako. Terrasse panoramique, cuisine américaine équipée, groupe électrogène. Accès rapide aux ambassades et ministères.',
                'type'              => 'duplex',
                'country'           => 'ML',
                'city'              => 'Bamako',
                'district'          => 'ACI 2000',
                'address'           => 'Avenue de l\'OUA, ACI 2000, Bamako',
                'capacity'          => 6,
                'bedrooms'          => 3,
                'bathrooms'         => 2,
                'area_sqm'          => 160,
                'price_per_night'   => 60000,
                'price_per_week'    => 380000,
                'price_per_month'   => 1200000,
                'deposit_amount'    => 120000,
                'booking_type'      => 'instant',
                'cancellation_policy'=> 'moderate',
                'allow_pets'        => false,
                'allow_smoking'     => false,
                'allow_parties'     => false,
                'check_in_time'     => '15:00',
                'check_out_time'    => '12:00',
                'status'            => 'active',
                'rating_avg'        => 4.6,
                'rating_count'      => 7,
                'views_count'       => 430,
                'is_featured'       => true,
                'amenities'         => ['wifi', 'air_conditioning', 'generator', 'parking', 'security', 'equipped_kitchen', 'workspace', 'hot_water', 'tv', 'balcony'],
            ],
            [
                'owner_id'          => $ownerModels[0]->id,
                'title'             => 'Appartement Riviera Golf',
                'description'       => 'Élégant appartement en face du golf de la Riviera. Déco contemporaine, terrasse, parking privé. Parfait pour les amateurs de golf et les familles aisées.',
                'type'              => 'apartment',
                'country'           => 'CI',
                'city'              => 'Abidjan',
                'district'          => 'Riviera Golf',
                'address'           => 'Résidence Le Golf, Riviera, Abidjan',
                'capacity'          => 5,
                'bedrooms'          => 3,
                'bathrooms'         => 2,
                'area_sqm'          => 110,
                'price_per_night'   => 55000,
                'price_per_week'    => 330000,
                'price_per_month'   => 1100000,
                'deposit_amount'    => 110000,
                'booking_type'      => 'instant',
                'cancellation_policy'=> 'moderate',
                'allow_pets'        => true,
                'allow_smoking'     => false,
                'allow_parties'     => false,
                'check_in_time'     => '14:00',
                'check_out_time'    => '12:00',
                'status'            => 'active',
                'rating_avg'        => 4.8,
                'rating_count'      => 9,
                'views_count'       => 670,
                'is_featured'       => true,
                'amenities'         => ['wifi', 'air_conditioning', 'parking', 'pool', 'security', 'equipped_kitchen', 'balcony', 'tv', 'washer', 'hot_water'],
            ],
            [
                'owner_id'          => $ownerModels[1]->id,
                'title'             => 'Studio Mermoz Dakar',
                'description'       => 'Studio cosy au calme à Mermoz. Proche de la VDN, idéal pour courts séjours. Tout équipé, Wi-Fi inclus.',
                'type'              => 'studio',
                'country'           => 'SN',
                'city'              => 'Dakar',
                'district'          => 'Mermoz',
                'address'           => 'Cité Mermoz, Dakar',
                'capacity'          => 2,
                'bedrooms'          => 1,
                'bathrooms'         => 1,
                'area_sqm'          => 30,
                'price_per_night'   => 12000,
                'price_per_week'    => 75000,
                'price_per_month'   => 250000,
                'deposit_amount'    => 24000,
                'booking_type'      => 'instant',
                'cancellation_policy'=> 'flexible',
                'allow_pets'        => false,
                'allow_smoking'     => false,
                'allow_parties'     => false,
                'check_in_time'     => '14:00',
                'check_out_time'    => '11:00',
                'status'            => 'active',
                'rating_avg'        => 4.3,
                'rating_count'      => 15,
                'views_count'       => 280,
                'is_featured'       => false,
                'amenities'         => ['wifi', 'air_conditioning', 'equipped_kitchen', 'hot_water'],
            ],
            [
                'owner_id'          => $ownerModels[2]->id,
                'title'             => 'Villa Hippodrome Bamako',
                'description'       => 'Charmante villa dans le quartier de l\'Hippodrome. Jardin, garage, groupe électrogène. Quartier calme et bien desservi.',
                'type'              => 'villa',
                'country'           => 'ML',
                'city'              => 'Bamako',
                'district'          => 'Hippodrome',
                'address'           => 'Rue 14, Hippodrome, Bamako',
                'capacity'          => 8,
                'bedrooms'          => 4,
                'bathrooms'         => 2,
                'area_sqm'          => 220,
                'price_per_night'   => 85000,
                'price_per_week'    => 520000,
                'price_per_month'   => 1800000,
                'deposit_amount'    => 170000,
                'booking_type'      => 'request',
                'cancellation_policy'=> 'moderate',
                'allow_pets'        => true,
                'allow_smoking'     => false,
                'allow_parties'     => true,
                'check_in_time'     => '15:00',
                'check_out_time'    => '12:00',
                'status'            => 'active',
                'rating_avg'        => 4.5,
                'rating_count'      => 6,
                'views_count'       => 380,
                'is_featured'       => false,
                'amenities'         => ['wifi', 'generator', 'parking', 'garden', 'security', 'air_conditioning', 'equipped_kitchen', 'hot_water'],
            ],
            [
                'owner_id'          => $ownerModels[0]->id,
                'title'             => 'Chambre Marcory Abidjan',
                'description'       => 'Chambre meublée dans une résidence sécurisée à Marcory. Idéal pour travailleurs et étudiants.',
                'type'              => 'room',
                'country'           => 'CI',
                'city'              => 'Abidjan',
                'district'          => 'Marcory',
                'address'           => 'Zone 4, Marcory, Abidjan',
                'capacity'          => 2,
                'bedrooms'          => 1,
                'bathrooms'         => 1,
                'area_sqm'          => 20,
                'price_per_night'   => 7000,
                'price_per_week'    => 42000,
                'price_per_month'   => 130000,
                'deposit_amount'    => 14000,
                'booking_type'      => 'instant',
                'cancellation_policy'=> 'flexible',
                'allow_pets'        => false,
                'allow_smoking'     => false,
                'allow_parties'     => false,
                'check_in_time'     => '14:00',
                'check_out_time'    => '12:00',
                'status'            => 'active',
                'rating_avg'        => 4.2,
                'rating_count'      => 25,
                'views_count'       => 195,
                'is_featured'       => false,
                'amenities'         => ['wifi', 'hot_water', 'security', 'tv'],
            ],
            [
                'owner_id'          => $ownerModels[2]->id,
                'title'             => 'Appartement Badalabougou Bamako',
                'description'       => 'Bel appartement meublé à Badalabougou, proche des hôpitaux et universités. Idéal pour professionnels de santé en mission.',
                'type'              => 'apartment',
                'country'           => 'ML',
                'city'              => 'Bamako',
                'district'          => 'Badalabougou',
                'address'           => 'Badalabougou Est, Bamako',
                'capacity'          => 3,
                'bedrooms'          => 2,
                'bathrooms'         => 1,
                'area_sqm'          => 65,
                'price_per_night'   => 28000,
                'price_per_week'    => 170000,
                'price_per_month'   => 580000,
                'deposit_amount'    => 56000,
                'booking_type'      => 'instant',
                'cancellation_policy'=> 'flexible',
                'allow_pets'        => false,
                'allow_smoking'     => false,
                'allow_parties'     => false,
                'check_in_time'     => '14:00',
                'check_out_time'    => '12:00',
                'status'            => 'active',
                'rating_avg'        => 4.4,
                'rating_count'      => 11,
                'views_count'       => 310,
                'is_featured'       => false,
                'amenities'         => ['wifi', 'air_conditioning', 'generator', 'equipped_kitchen', 'hot_water', 'workspace'],
            ],
        ];

        $propertyModels = [];
        foreach ($propertiesData as $propData) {
            $amenities  = $propData['amenities'];
            $photosBase = 'https://placehold.co/800x600/F59E0B/FFFFFF?text=' . urlencode(substr($propData['title'], 0, 20));

            unset($propData['amenities']);

            $property = Property::create($propData);

            // Add placeholder photos
            for ($i = 1; $i <= 4; $i++) {
                PropertyPhoto::create([
                    'property_id' => $property->id,
                    'path'        => "properties/photos/demo_{$property->id}_{$i}.jpg",
                    'caption'     => null,
                    'sort_order'  => $i,
                    'is_cover'    => $i === 1,
                ]);
            }

            // Add amenities
            foreach ($amenities as $amenity) {
                PropertyAmenity::create([
                    'property_id' => $property->id,
                    'amenity'     => $amenity,
                ]);
            }

            $propertyModels[] = $property;
        }

        // ─── BOOKINGS ─────────────────────────────────────────────────────────
        $bookingsData = [
            [
                'property_idx'   => 0,
                'tenant_idx'     => 0,
                'check_in'       => Carbon::today()->addDays(5)->toDateString(),
                'check_out'      => Carbon::today()->addDays(10)->toDateString(),
                'nights'         => 5,
                'guests'         => 2,
                'status'         => 'confirmed',
                'payment_status' => 'escrowed',
            ],
            [
                'property_idx'   => 1,
                'tenant_idx'     => 1,
                'check_in'       => Carbon::today()->addDays(2)->toDateString(),
                'check_out'      => Carbon::today()->addDays(7)->toDateString(),
                'nights'         => 5,
                'guests'         => 3,
                'status'         => 'pending',
                'payment_status' => 'pending',
            ],
            [
                'property_idx'   => 2,
                'tenant_idx'     => 2,
                'check_in'       => Carbon::today()->subDays(10)->toDateString(),
                'check_out'      => Carbon::today()->subDays(3)->toDateString(),
                'nights'         => 7,
                'guests'         => 6,
                'status'         => 'completed',
                'payment_status' => 'released',
            ],
            [
                'property_idx'   => 3,
                'tenant_idx'     => 3,
                'check_in'       => Carbon::today()->addDays(15)->toDateString(),
                'check_out'      => Carbon::today()->addDays(18)->toDateString(),
                'nights'         => 3,
                'guests'         => 1,
                'status'         => 'confirmed',
                'payment_status' => 'escrowed',
            ],
            [
                'property_idx'   => 4,
                'tenant_idx'     => 4,
                'check_in'       => Carbon::today()->subDays(20)->toDateString(),
                'check_out'      => Carbon::today()->subDays(10)->toDateString(),
                'nights'         => 10,
                'guests'         => 4,
                'status'         => 'completed',
                'payment_status' => 'released',
            ],
        ];

        foreach ($bookingsData as $bData) {
            $property = $propertyModels[$bData['property_idx']];
            $tenant   = $tenantModels[$bData['tenant_idx']];
            $nights   = $bData['nights'];
            $subtotal = $nights * $property->price_per_night;
            $fee      = round($subtotal * 0.03);
            $comm     = round($subtotal * 0.08);
            $total    = $subtotal + $fee + $property->deposit_amount;

            Booking::create([
                'reference'           => Booking::generateReference(),
                'property_id'         => $property->id,
                'tenant_id'           => $tenant->id,
                'owner_id'            => $property->owner_id,
                'check_in'            => $bData['check_in'],
                'check_out'           => $bData['check_out'],
                'nights'              => $nights,
                'guests'              => $bData['guests'],
                'price_per_night'     => $property->price_per_night,
                'subtotal'            => $subtotal,
                'service_fee'         => $fee,
                'platform_commission' => $comm,
                'deposit_amount'      => $property->deposit_amount,
                'total_amount'        => $total,
                'status'              => $bData['status'],
                'payment_status'      => $bData['payment_status'],
                'confirmed_at'        => in_array($bData['status'], ['confirmed', 'completed']) ? now() : null,
            ]);
        }

        $this->command->info('✓ Base de données initialisée avec succès !');
        $this->command->info('  Admin : admin@koloimmo.com / Admin@2026');
        $this->command->info('  Propriétaires : kofi.mensah@demo.com, aminata.diallo@demo.com, ibrahim.coulibaly@demo.com / Owner@2026');
        $this->command->info('  Locataires : aicha.traore@demo.com... / Tenant@2026');
        $this->command->info('  ' . count($propertyModels) . ' logements créés');
        $this->command->info('  ' . count($bookingsData) . ' réservations créées');
    }
}
