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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            // Shipping Info (snapshot from address at time of order)
            $table->string('recipient_name');
            $table->string('recipient_phone');
            $table->text('shipping_address');
            $table->foreignId('province_id')->nullable()->constrained();
            $table->string('province_name');
            $table->decimal('shipping_fee', 8, 2);

            // Payment Info
            $table->enum('payment_method', ['cod', 'khqr']);
            $table->enum('payment_status', ['pending', 'paid', 'failed'])->default('pending');
            $table->string('payment_proof')->nullable();

            // Pricing
            $table->decimal('subtotal', 10, 2);
            $table->decimal('discount_amount', 10, 2)->default(0.00);
            $table->string('discount_source')->nullable();
            $table->decimal('total', 10, 2);

            // Order Status
            $table->enum('status', ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'])->default('pending');

            // Timestamps
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('order_number');
            $table->index('user_id');
            $table->index('recipient_phone');
            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
