// database/migrations/2025_05_26_175933_add_password_to_health_workers_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('health_workers', function (Blueprint $table) {
            // This line adds the password column
            $table->string('password')->nullable()->after('email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('health_workers', function (Blueprint $table) {
            $table->dropColumn('password');
        });
    }
};