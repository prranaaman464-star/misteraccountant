<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->prefix('inventory')->group(function () {
    Route::get('items', function () {
        return Inertia::render('inventory/Items');
    })->name('inventory.items');
    Route::get('items/create', function () {
        return Inertia::render('inventory/Items/Create');
    })->name('inventory.items.create');
    Route::post('items', function () {
        return Inertia::render('inventory/Items/Store');
    })->name('inventory.items.store');
    Route::get('items/{item}', function () {
        return Inertia::render('inventory/Items/Show');
    })->name('inventory.items.show');
    Route::get('items/{item}/edit', function () {
        return Inertia::render('inventory/Items/Edit');
    })->name('inventory.items.edit');
    Route::put('items/{item}', function () {
        return Inertia::render('inventory/Items/Update');
    })->name('inventory.items.update');
    Route::delete('items/{item}', function () {
        return Inertia::render('inventory/Items/Destroy');
    })->name('inventory.items.destroy');
});

require __DIR__.'/settings.php';
