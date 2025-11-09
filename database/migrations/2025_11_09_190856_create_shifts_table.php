<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shifts', function (Blueprint $table) {
            $table->id();
            $table->string('property_code', 50)->index();

            // shift code (e.g., M, A, N) and name
            $table->string('code', 10);
            $table->string('name', 150);

            // times (use TIME format)
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();

            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // unique per property
            $table->unique(['property_code', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shifts');
    }
};
