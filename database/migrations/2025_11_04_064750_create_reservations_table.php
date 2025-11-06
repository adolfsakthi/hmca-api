<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('room_id')->nullable()->constrained('rooms')->onDelete('restrict');
            $table->string('property_code');
            $table->dateTime('check_in');
            $table->dateTime('check_out');
            $table->string('arrival_from')->nullable();
            $table->enum('booking_type', ['online', 'walk-in', 'corporate'])->default('walk-in');
            $table->string('source_of_booking')->default('pms');
            $table->string('booking_reference_no')->nullable();
            $table->string('purpose_of_visit')->nullable();
            $table->text('remarks')->nullable();
            $table->unsignedTinyInteger('adults')->default(1);
            $table->unsignedTinyInteger('children')->default(0);
            $table->string('country_code')->nullable();
            $table->string('mobile_no')->nullable();
            $table->string('title')->nullable();
            $table->string('first_name');
            $table->string('last_name')->nullable();
            $table->string('father_name')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->string('occupation')->nullable();
            $table->date('dob')->nullable();
            $table->date('anniversary')->nullable();
            $table->string('nationality')->nullable();
            $table->boolean('is_vip')->default(false);
            $table->string('contact_type')->nullable();
            $table->string('email')->nullable();
            $table->string('country')->nullable();
            $table->string('state')->nullable();
            $table->string('city')->nullable();
            $table->string('zipcode')->nullable();
            $table->text('address')->nullable();
            $table->string('identity_type')->nullable();
            $table->string('identity_no');
            $table->string('front_doc')->nullable();
            $table->string('back_doc')->nullable();
            $table->text('identity_comments')->nullable();
            $table->string('guest_image')->nullable();
            $table->string('discount_reason')->nullable();
            $table->decimal('discount_percent', 5, 2)->nullable();
            $table->decimal('commission_percent', 5, 2)->nullable();
            $table->decimal('commission_amount', 10, 2)->nullable();
            $table->enum('payment_mode', ['cash', 'card', 'upi', 'bank-transfer'])->nullable();
            $table->decimal('advance_amount', 10, 2)->default(0);
            $table->text('advance_remarks')->nullable();
            $table->decimal('booking_charge', 10, 2)->default(0);
            $table->decimal('tax', 10, 2)->default(0);
            $table->decimal('service_charge', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);
            $table->enum('status', ['reserved', 'checked-in', 'checked-out', 'cancelled'])->default('reserved');
            $table->foreign('property_code')->references('property_code')->on('properties')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
