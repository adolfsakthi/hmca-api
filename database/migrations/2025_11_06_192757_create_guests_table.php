<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('guests', function (Blueprint $table) {
            $table->id();

            $table->string('property_code');
            $table->foreign('property_code')
                ->references('property_code')
                ->on('properties')
                ->onDelete('cascade');

            // 🧍 Basic Info
            $table->string('title')->nullable();
            $table->string('name');
            $table->string('father_name')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->string('occupation')->nullable();
            $table->date('dob')->nullable();
            $table->date('anniversary')->nullable();
            $table->string('nationality')->nullable();

            // 📞 Contact Info
            $table->string('country_code', 10)->nullable();
            $table->string('mobile_no', 20)->nullable();
            $table->string('email')->nullable();
            $table->string('country')->nullable();
            $table->string('state')->nullable();
            $table->string('city')->nullable();
            $table->string('zipcode')->nullable();
            $table->text('address')->nullable();

            // 🪪 Identity
            $table->string('identity_type')->nullable();
            $table->string('identity_no')->nullable();
            $table->string('front_doc')->nullable();
            $table->string('back_doc')->nullable();
            $table->text('identity_comments')->nullable();
            $table->string('guest_image')->nullable();

            // 🏅 Flags
            $table->boolean('is_vip')->default(false);
            $table->boolean('is_blacklisted')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guests');
    }
};
