<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_approvals', function (Blueprint $table) {
            $table->id();

            // property_code references properties.code
            $table->string('property_code', 50);
            $table->foreign('property_code')
                ->references('property_code')
                ->on('properties')
                ->onDelete('cascade');

            // Foreign key to leaves table
            $table->unsignedBigInteger('leave_id');
            $table->foreign('leave_id')
                ->references('id')
                ->on('leaves')
                ->onDelete('cascade');

            // Foreign key to users table (the approver)
            $table->unsignedBigInteger('performed_by')->nullable();
            $table->foreign('performed_by')
                ->references('id')
                ->on('users')
                ->onDelete('set null');

            // Action info
            $table->string('action', 50);
            $table->timestamp('performed_at')->nullable();
            $table->text('remarks')->nullable();

            // JSON metadata (can store extra info like device, IP, etc.)
            $table->json('meta')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_approvals');
    }
};
