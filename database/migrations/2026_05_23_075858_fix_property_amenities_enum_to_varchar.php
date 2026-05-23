<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE property_amenities MODIFY COLUMN amenity VARCHAR(100) NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE property_amenities MODIFY COLUMN amenity ENUM(
            'wifi','air_conditioning','equipped_kitchen','parking','generator',
            'pool','gym','security','tv','washer','dryer','iron','workspace',
            'balcony','garden','elevator','hot_water','solar_power'
        ) NOT NULL");
    }
};
