<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('pos_sales', function (Blueprint $table) {
            $table->id();
            $table->string('property_code');
            $table->string('invoice_no')->unique();
            $table->foreignId('reservation_id')->nullable()->constrained('reservations')->nullOnDelete();

            // guest contact fields for Pay Later verification
            $table->string('customer_name')->nullable();
            $table->string('customer_email')->nullable();
            $table->string('customer_phone')->nullable();

            $table->decimal('subtotal', 10, 2);
            $table->decimal('tax', 10, 2)->default(0);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('total', 10, 2);
            $table->enum('payment_mode', ['Pay Later','Cash','Card','UPI'])->default('Pay Later');
            $table->string('status')->default('open'); // open, settled, cancelled
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('pos_sales'); }
};
