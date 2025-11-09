<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('property_code')->nullable();
            $table->string('name');
            $table->string('slug');// e.g. 'admin', 'user'
            $table->text('description')->nullable();
            $table->timestamps();
            $table->foreign('property_code')->references('property_code')->on('properties')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
