<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('essl_logs', function (Blueprint $table) {
            $table->id();
            $table->string('property_code', 50)->index();
            $table->foreignId('device_id')->constrained('essl_devices')->cascadeOnDelete();
            $table->dateTime('log_datetime')->index();
            $table->string('employee_code', 100)->nullable();
            $table->string('verify_mode', 50)->nullable();
            $table->string('in_out_mode', 50)->nullable();
            $table->text('raw_payload')->nullable();
            $table->timestamps();

            $table->unique(['device_id','log_datetime','employee_code'], 'uniq_device_log');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('essl_logs');
    }
};
