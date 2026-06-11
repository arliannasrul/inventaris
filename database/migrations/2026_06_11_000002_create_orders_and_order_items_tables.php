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
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->string('customer_name');
            $table->string('customer_phone');
            $table->text('customer_address');
            $table->integer('destination_city_id');
            $table->string('destination_city_name');
            $table->integer('weight_grams');
            $table->string('courier'); // jne, jnt, sicepat, anteraja
            $table->string('shipping_service'); // REG, OKE, YES, etc.
            $table->decimal('shipping_cost', 12, 2);
            $table->string('status')->default('pending'); // pending, processing, shipping, delivered, cancelled
            $table->string('waybill')->nullable(); // resi dari Kiriminaja
            $table->string('kiriminaja_order_id')->nullable();
            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            $table->integer('quantity');
            $table->decimal('price', 12, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
