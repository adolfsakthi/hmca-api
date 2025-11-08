<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->string('property_code');
            $table->unsignedBigInteger('room_type_id');
            $table->string('room_number');
            $table->integer('capacity')->default(1);
            $table->integer('extra_capability')->nullable();
            $table->decimal('room_price', 10, 2)->default(0);
            $table->decimal('bed_charge', 10, 2)->nullable();
            $table->enum('room_size', ['single', 'double', 'tripal', 'king', 'queen', 'quad', 'others']);
            $table->integer('bed_number')->nullable();
            $table->enum('bed_type', ['kingbed', 'queenbed', 'electricbed', 'futonbed', 'mattressbed', 'airbed']);
            $table->text('room_description')->nullable();
            $table->text('reserve_condition')->nullable();
            $table->boolean('is_active')->default(true);
            $table->enum('status', ['vacant', 'reserved', 'occupied', 'dirty', 'cleaning', 'maintenance'])->default('vacant');
            $table->timestamps();
            $table->foreign('property_code')->references('property_code')->on('properties')->onDelete('cascade');
            $table->foreign('room_type_id')
                ->references('id')->on('room_types')
                ->onDelete('restrict');
            $table->unique(['property_code', 'room_number']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('rooms');
    }
};
