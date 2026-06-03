<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

// Ensure clean state
try {
    Schema::dropIfExists('items');
    echo "Dropped items table\n";
} catch (\Exception $e) {
    echo "Drop error: " . $e->getMessage() . "\n";
}

// Let's run individual parts of the migration manually
try {
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
    echo "Laravel Schema::create succeeded!\n";
} catch (\Exception $e) {
    echo "Laravel Schema::create failed!\n";
    echo "Error message: " . $e->getMessage() . "\n";
    if ($e->getPrevious()) {
        echo "Previous error: " . $e->getPrevious()->getMessage() . "\n";
    }
}
