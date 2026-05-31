<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Modify role ENUM to include 'agent'
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('tenant','owner','both','admin','agent') NOT NULL DEFAULT 'tenant'");
    }

    public function down(): void
    {
        // Revert 'agent' users to 'tenant' before removing from ENUM
        DB::statement("UPDATE users SET role = 'tenant' WHERE role = 'agent'");
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('tenant','owner','both','admin') NOT NULL DEFAULT 'tenant'");
    }
};
