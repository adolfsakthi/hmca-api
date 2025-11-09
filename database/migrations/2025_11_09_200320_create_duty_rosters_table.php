<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('duty_rosters', function (Blueprint $table) {
            $table->id();

            // Every record must belong to a property
            $table->string('property_code', 50)->index();

            // Relations
            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('shift_id')->nullable();

            // Duty date
            $table->date('roster_date')->index();

            // Optional shift timing override (if different from default shift)
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();

            // Notes or remarks (optional)
            $table->text('note')->nullable();

            // System timestamps
            $table->timestamps();
            $table->softDeletes();

            // Uniqueness per property + employee + date
            $table->unique(['property_code', 'employee_id', 'roster_date'], 'uniq_property_emp_date');

            // Foreign key constraints (optional for soft link validation)
            // You can uncomment these if you have the employee and shift tables in same DB
            // $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            // $table->foreign('shift_id')->references('id')->on('shifts')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('duty_rosters');
    }
};
