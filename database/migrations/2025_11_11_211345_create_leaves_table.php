<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leaves', function (Blueprint $table) {
            $table->id();

            // Foreign key to properties table (property_code → properties.code)
            $table->string('property_code', 50);
            $table->foreign('property_code')
                ->references('property_code')
                ->on('properties')
                ->onDelete('cascade');

            // Employee and Leave Type relations
            $table->unsignedBigInteger('employee_id');
            $table->foreign('employee_id')
                ->references('id')
                ->on('employees')
                ->onDelete('cascade');

            $table->unsignedBigInteger('leave_type_id');
            $table->foreign('leave_type_id')
                ->references('id')
                ->on('leave_types')
                ->onDelete('cascade');

            // Leave details
            $table->enum('duration_unit', ['full', '3/4', 'half', '1/4'])->default('full');
            $table->date('from_date');
            $table->date('to_date');
            $table->text('remarks')->nullable();

            // Leave status
            $table->string('status', 50);

            // Department approval fields
            $table->unsignedBigInteger('dept_approved_by')->nullable();
            $table->timestamp('dept_approved_at')->nullable();
            $table->text('dept_approval_remarks')->nullable();
            $table->foreign('dept_approved_by')
                ->references('id')
                ->on('users')
                ->onDelete('set null');

            // HR approval fields
            $table->unsignedBigInteger('hr_approved_by')->nullable();
            $table->timestamp('hr_approved_at')->nullable();
            $table->text('hr_approval_remarks')->nullable();
            $table->foreign('hr_approved_by')
                ->references('id')
                ->on('users')
                ->onDelete('set null');

            // Flags
            $table->boolean('is_approved')->default(false);
            $table->timestamp('processed_at')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leaves');
    }
};
