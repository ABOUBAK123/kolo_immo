<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Expand the type ENUM to include 'house'
        DB::statement("ALTER TABLE properties MODIFY COLUMN type ENUM('studio','apartment','villa','room','duplex','house') NOT NULL DEFAULT 'apartment'");

        // Make address nullable (field is optional in the form)
        Schema::table('properties', function (Blueprint $table) {
            $table->text('address')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->text('address')->nullable(false)->change();
        });

        DB::statement("ALTER TABLE properties MODIFY COLUMN type ENUM('studio','apartment','villa','room','duplex') NOT NULL DEFAULT 'apartment'");
    }
};
