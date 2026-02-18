<?php

use App\Http\Controllers\Auth\CheckEmailController;
use App\Http\Controllers\Auth\EmailController;
use App\Http\Controllers\CurrentOrganizationController;
use App\Http\Controllers\GetStarted\PlanController as GetStartedPlanController;
use App\Http\Controllers\Manage\ManageController;
use App\Http\Controllers\Onboarding\OrganizationController;
use App\Http\Controllers\Onboarding\PlanController;
use App\Http\Controllers\PlanDetailController;
use App\Http\Controllers\PostAuthRedirectController;
use App\Http\Controllers\RenewSubscriptionController;
use App\Http\Middleware\RedirectIfNoOrganization;
use App\Http\Controllers\InventoryItemsController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

// SaasyKit-style: Plan first (public), then email, then login or signup
Route::get('plans', [GetStartedPlanController::class, 'index'])->name('plans.index');
Route::post('plans/select', [GetStartedPlanController::class, 'select'])->name('plans.select');

Route::middleware('guest')->group(function () {
    Route::get('auth/email', [EmailController::class, 'show'])->name('auth.email');
    Route::post('check-email', [CheckEmailController::class, 'check'])->name('check-email');
});

Route::get('after-auth', PostAuthRedirectController::class)->middleware(['auth', 'verified'])->name('after-auth');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::middleware([RedirectIfNoOrganization::class])->group(function () {
        Route::get('dashboard', function () {
            return Inertia::render('Dashboard');
        })->name('dashboard');
        Route::get('plan', PlanDetailController::class)->name('plan.show');
        Route::post('plan/renew', RenewSubscriptionController::class)->name('plan.renew');
        Route::put('current-organization', [CurrentOrganizationController::class, 'update'])->name('current-organization.update');

        Route::prefix('manage')->name('manage.')->group(function () {
            Route::get('/', [ManageController::class, 'index'])->name('index');
            Route::get('users', [ManageController::class, 'users'])->name('users');
            Route::post('members', [ManageController::class, 'storeMember'])->name('members.store');
            Route::get('memberships', [ManageController::class, 'memberships'])->name('memberships');
            Route::get('permissions', [ManageController::class, 'permissions'])->name('permissions');
            Route::post('permissions', [ManageController::class, 'storePermission'])->name('permissions.store');
        });
    });

    Route::prefix('onboarding')->name('onboarding.')->group(function () {
        Route::get('organizations/create', [OrganizationController::class, 'create'])->name('organizations.create');
        Route::post('organizations', [OrganizationController::class, 'store'])->name('organizations.store');
        Route::get('plans', [PlanController::class, 'index'])->name('plans.index');
        Route::post('plans', [PlanController::class, 'store'])->name('plans.store');
    });
});

Route::middleware(['auth', 'verified', RedirectIfNoOrganization::class])->prefix('inventory')->group(function () {
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
Route::middleware(['auth', 'verified'])->prefix('inventory')->group(function () {
    Route::get('items', [InventoryItemsController::class, 'index'])->name('inventory.items');
    Route::get('items/create', [InventoryItemsController::class, 'create'])->name('inventory.items.create');
    Route::post('items', [InventoryItemsController::class, 'store'])->name('inventory.items.store');
    Route::post('categories', [InventoryItemsController::class, 'storeCategory'])->name('inventory.categories.store');
    Route::get('items/{item}', [InventoryItemsController::class, 'show'])->name('inventory.items.show');
    Route::get('items/{item}/edit', [InventoryItemsController::class, 'edit'])->name('inventory.items.edit');
    Route::put('items/{item}', [InventoryItemsController::class, 'update'])->name('inventory.items.update');
    Route::delete('items/{item}', [InventoryItemsController::class, 'destroy'])->name('inventory.items.destroy');
});

require __DIR__.'/settings.php';
