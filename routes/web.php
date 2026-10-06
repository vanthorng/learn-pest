<?php

use App\Http\Controllers\ChartOfAccountController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Teams\TeamInvitationController;
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
    });

Route::middleware(['auth'])->group(function () {
    Route::post('invitations/{invitation}/accept', [TeamInvitationController::class, 'accept'])->name('invitations.accept');
    Route::delete('invitations/{invitation}', [TeamInvitationController::class, 'decline'])->name('invitations.decline');
});

require __DIR__.'/settings.php';
