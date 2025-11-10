<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('essl_devices', function (Blueprint $table) {
            $table->id();
            $table->string('property_code', 50)->index();
            $table->string('device_name', 150);
            $table->string('serial_number', 150)->nullable();
            $table->string('ip_address', 200); // allow ip:port or hostname
            $table->unsignedSmallInteger('port')->nullable();
            $table->string('username', 100)->nullable();
            $table->text('password')->nullable(); // encrypted
            $table->string('location', 150)->nullable();
            $table->enum('status', ['online', 'offline'])->default('offline');
            $table->timestamp('last_ping_at')->nullable();
            $table->timestamp('last_sync_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['property_code', 'ip_address'], 'uniq_prop_ip');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('essl_devices');
    }
};
