<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('essl_devices', function (Blueprint $table) {
            // Drop the unique constraint by its name
            $table->dropUnique('uniq_prop_ip');
        });
    }

    public function down(): void
    {
        Schema::table('essl_devices', function (Blueprint $table) {
            // Re-add it if migration is rolled back
            $table->unique(['property_code', 'ip_address'], 'uniq_prop_ip');
        });
    }
};
