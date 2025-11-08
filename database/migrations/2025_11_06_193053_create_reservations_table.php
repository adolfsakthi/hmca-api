<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();

            // 🔗 Property, Room, and Guest
            $table->string('property_code');
            $table->foreign('property_code')
                ->references('property_code')
                ->on('properties')
                ->onDelete('cascade');

            $table->foreignId('room_id')->constrained('rooms')->onDelete('cascade');
            $table->foreignId('guest_id')->constrained('guests')->onDelete('cascade');

            // 📅 Stay Details
            $table->dateTime('check_in');
            $table->dateTime('check_out');

            // 🧾 Booking Info
            $table->string('arrival_from')->nullable();
            $table->enum('booking_type', ['Walk-in', 'Online', 'Corporate'])->default('Walk-in'); // Online / Walk-in / OTA
            $table->string('booking_reference_no')->nullable();
            $table->string('purpose_of_visit')->nullable();
            $table->text('remarks')->nullable();

            // Occupancy
            $table->integer('adults')->default(1);
            $table->integer('children')->default(0);

            // 🧍 Booking Source
            $table->string('source_of_booking')->default('pms'); // Direct / Expedia / Agent

            // 💰 Charges & Financial Info
            $table->decimal('booking_charge', 10, 2)->default(0);
            $table->decimal('discount_percent', 5, 2)->nullable();
            $table->string('discount_reason')->nullable();
            $table->decimal('commission_percent', 5, 2)->nullable();
            $table->decimal('commission_amount', 10, 2)->nullable();
            $table->decimal('tax', 10, 2)->default(0);
            $table->decimal('service_charge', 10, 2)->default(0);
            $table->decimal('paid_amount', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);

            // 💳 Payments
            $table->string('payment_mode')->nullable(); // Cash / Card / UPI
            $table->decimal('advance_amount', 10, 2)->default(0);
            $table->text('advance_remarks')->nullable();

            // 🚦 Status
            $table->enum('status', [
                'reserved',
                'checked_in',
                'checked_out',
                'cancelled'
            ])->default('reserved');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
