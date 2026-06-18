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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('order_id')->unique();          // ID unik untuk DOKU
            $table->string('invoice_number')->nullable();  // Invoice dari DOKU
            $table->unsignedInteger('amount');             // Dalam Rupiah
            $table->string('plan');                        // 'monthly' | 'yearly'
            $table->string('status')->default('pending');  // pending | paid | failed | expired
            $table->text('doku_payment_url')->nullable();  // URL DOKU Checkout
            $table->json('doku_response')->nullable();     // Raw response dari DOKU
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index('order_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
