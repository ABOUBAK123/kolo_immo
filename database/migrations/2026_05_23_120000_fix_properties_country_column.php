<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE properties DROP INDEX properties_status_country_city_index");
        DB::statement("ALTER TABLE properties MODIFY COLUMN country VARCHAR(100) NOT NULL DEFAULT 'CI'");
        DB::statement("ALTER TABLE properties ADD INDEX properties_status_country_city_index (status, country(20), city(20))");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE properties DROP INDEX properties_status_country_city_index");
        DB::statement("ALTER TABLE properties MODIFY COLUMN country VARCHAR(2) NOT NULL DEFAULT 'CI'");
        DB::statement("ALTER TABLE properties ADD INDEX properties_status_country_city_index (status, country, city)");
    }
};
