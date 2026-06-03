<?php

use Illuminate\Support\Facades\Artisan;

Artisan::command('inventory:ping', function () {
    $this->info('Inventory console is ready.');
});
