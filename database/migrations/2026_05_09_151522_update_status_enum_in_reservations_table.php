<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Update the ENUM to include 'done'
        DB::statement("ALTER TABLE reservations MODIFY COLUMN status ENUM('pending', 'approved', 'rejected', 'done') NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        // Revert back if we ever need to rollback
        DB::statement("ALTER TABLE reservations MODIFY COLUMN status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending'");
    }
};