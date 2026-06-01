<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlansSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name'                     => 'Starter',
                'slug'                     => 'starter',
                'description'              => 'Parfait pour débuter avec 1 logement.',
                'price_monthly'            => 0,
                'max_properties'           => 1,
                'max_photos_per_property'  => 5,
                'featured_listing'         => false,
                'analytics_advanced'       => false,
                'priority_support'         => false,
                'is_active'                => true,
                'sort_order'               => 1,
            ],
            [
                'name'                     => 'Pro',
                'slug'                     => 'pro',
                'description'              => 'Pour les propriétaires actifs avec plusieurs biens.',
                'price_monthly'            => 5000,
                'max_properties'           => 5,
                'max_photos_per_property'  => 15,
                'featured_listing'         => true,
                'analytics_advanced'       => true,
                'priority_support'         => false,
                'is_active'                => true,
                'sort_order'               => 2,
            ],
            [
                'name'                     => 'Premium',
                'slug'                     => 'premium',
                'description'              => 'Accès illimité, support dédié et visibilité maximale.',
                'price_monthly'            => 15000,
                'max_properties'           => 999,
                'max_photos_per_property'  => 30,
                'featured_listing'         => true,
                'analytics_advanced'       => true,
                'priority_support'         => true,
                'is_active'                => true,
                'sort_order'               => 3,
            ],
        ];

        foreach ($plans as $data) {
            Plan::updateOrCreate(['slug' => $data['slug']], $data);
        }
    }
}
