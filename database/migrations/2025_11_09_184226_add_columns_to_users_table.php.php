<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {

            $table->unsignedBigInteger('role_id')->nullable()->after('id');
            $table->foreign('role_id')
                ->references('id')
                ->on('roles')
                ->onDelete('set null');
            $table->string('property_code')->nullable();
            $table->foreign('property_code')
                ->references('property_code')
                ->on('properties')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropForeign(['property_code']);
            $table->dropColumn(['role_id', 'property_code']);
        });
    }
};
