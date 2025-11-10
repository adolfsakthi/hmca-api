<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roster_imports', function (Blueprint $table) {
            $table->id();
            $table->string('property_code', 50)->index();
            $table->string('file_path');
            $table->unsignedBigInteger('uploaded_by')->nullable();
            $table->integer('total_rows')->default(0);
            $table->integer('processed_count')->default(0);
            $table->integer('error_count')->default(0);
            $table->json('errors')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('roster_imports');
    }
};
