<?php

use App\Http\Controllers\ChartOfAccountController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\Teams\TeamInvitationController;
use App\Http\Controllers\VendorController;
use App\Http\Middleware\EnsureTeamMembership;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

Route::prefix('{current_team}')
    ->middleware(['auth', 'verified', EnsureTeamMembership::class])
    ->scopeBindings()
    ->group(function () {
        Route::get('dashboard', DashboardController::class)->name('dashboard');
        Route::get('chart-of-accounts', [ChartOfAccountController::class, 'index'])->name('chart-of-accounts.index');
        Route::post('chart-of-accounts', [ChartOfAccountController::class, 'store'])->name('chart-of-accounts.store');
        Route::patch('chart-of-accounts/{chartOfAccount}', [ChartOfAccountController::class, 'update'])->name('chart-of-accounts.update');
        Route::delete('chart-of-accounts/{chartOfAccount}', [ChartOfAccountController::class, 'destroy'])->name('chart-of-accounts.destroy');
        Route::get('vendors', [VendorController::class, 'index'])->name('vendors.index');
        Route::post('vendors', [VendorController::class, 'store'])->name('vendors.store');
        Route::patch('vendors/{vendor}', [VendorController::class, 'update'])->name('vendors.update');
        Route::delete('vendors/{vendor}', [VendorController::class, 'destroy'])->name('vendors.destroy');
        Route::get('customers', [CustomerController::class, 'index'])->name('customers.index');
        Route::post('customers', [CustomerController::class, 'store'])->name('customers.store');
        Route::patch('customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');
        Route::delete('customers/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy');
        Route::get('items', [ItemController::class, 'index'])->name('items.index');
        Route::post('items', [ItemController::class, 'store'])->name('items.store');
        Route::patch('items/{item}', [ItemController::class, 'update'])->name('items.update');
        Route::delete('items/{item}', [ItemController::class, 'destroy'])->name('items.destroy');
    });

Route::middleware(['auth'])->group(function () {
    Route::post('invitations/{invitation}/accept', [TeamInvitationController::class, 'accept'])->name('invitations.accept');
    Route::delete('invitations/{invitation}', [TeamInvitationController::class, 'decline'])->name('invitations.decline');
});

require __DIR__.'/settings.php';
