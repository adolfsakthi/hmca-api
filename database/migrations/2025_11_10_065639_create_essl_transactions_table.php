<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('essl_transactions', function (Blueprint $table) {
            $table->id();

            // link to property (always required)
            $table->string('property_code', 50)->index();

            // optional link to device if known
            $table->foreignId('device_id')->nullable()->constrained('essl_devices')->nullOnDelete();

            // employee identifier as returned from device (pin / code)
            $table->string('employee_code', 150)->index();

            // the actual punch datetime reported by device
            $table->dateTime('punch_at')->index();

            // raw single-line text from the response (for debugging)
            $table->text('raw_line')->nullable();

            // optional parsed JSON payload
            $table->json('raw_payload')->nullable();

            // mark if this row already processed into attendance
            $table->boolean('processed')->default(false);
            $table->timestamp('processed_at')->nullable();

            $table->timestamps();

            // prevent exact duplicate inserts for same device+employee+timestamp
            $table->unique(['device_id', 'employee_code', 'punch_at'], 'uniq_device_emp_punch');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('essl_transactions');
    }
};
