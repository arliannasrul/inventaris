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
            $table->boolean('is_premium')->default(false)->after('avatar');
            $table->string('premium_plan')->nullable()->after('is_premium'); // 'monthly' | 'yearly'
            $table->timestamp('premium_expires_at')->nullable()->after('premium_plan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['is_premium', 'premium_plan', 'premium_expires_at']);
        });
    }
};
