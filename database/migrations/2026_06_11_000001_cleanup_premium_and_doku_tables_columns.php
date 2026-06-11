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
        Schema::dropIfExists('payments');

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'is_premium')) {
                $table->dropColumn('is_premium');
            }
            if (Schema::hasColumn('users', 'premium_plan')) {
                $table->dropColumn('premium_plan');
            }
            if (Schema::hasColumn('users', 'premium_expires_at')) {
                $table->dropColumn('premium_expires_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_premium')->default(false);
            $table->string('premium_plan')->nullable();
            $table->timestamp('premium_expires_at')->nullable();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('order_id')->unique();
            $table->string('invoice_number')->nullable();
            $table->string('status')->default('PENDING');
            $table->decimal('amount', 12, 2);
            $table->string('plan');
            $table->text('doku_payment_url')->nullable();
            $table->json('doku_response')->nullable();
            $table->timestamps();
        });
    }
};
