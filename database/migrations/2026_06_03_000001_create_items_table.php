<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public $withinTransaction = false;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('sku', 80)->unique();
            $table->string('name', 160);
            $table->string('category', 120)->index();
            $table->string('location', 120)->index();
            $table->string('supplier', 120)->nullable();
            $table->string('unit', 40)->default('pcs');
            $table->integer('quantity')->default(0);
            $table->integer('minimum_stock')->default(0);
            $table->decimal('unit_price', 14, 2)->default(0.00);
            $table->text('notes')->nullable();
            $table->string('image_url', 2048)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
