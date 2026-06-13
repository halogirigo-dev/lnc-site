<?php

use Illuminate\Support\Facades\Route;

// Filament admin panel is registered via AdminPanelProvider
// This file redirects root to the admin panel
Route::get('/', function () {
    return redirect('/admin');
});
