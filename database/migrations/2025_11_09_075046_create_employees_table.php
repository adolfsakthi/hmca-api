<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();

            // multi-property requirement
            $table->string('property_code', 50)->index();

            // basic employee info
            $table->string('first_name', 100);
            $table->string('last_name', 100)->nullable();
            $table->string('email')->nullable()->index();
            $table->string('employee_code', 50); // human code like EMP001

            $table->string('department', 100)->nullable();
            $table->string('designation', 150)->nullable();

            // shift (simple, not full shift master)
            $table->time('shift_start_time')->nullable();
            $table->time('shift_end_time')->nullable();

            $table->date('date_of_joining')->nullable();
            $table->string('outlet', 100)->nullable();

            $table->string('avatar')->nullable(); // optional user image
            $table->json('meta')->nullable();     // future proofing

            $table->timestamps();
            $table->softDeletes();

            // enforce per-property uniqueness
            $table->unique(['property_code', 'employee_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};