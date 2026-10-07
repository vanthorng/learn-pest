<?php

use App\Http\Controllers\Api\V1\CustomerController;
use App\Http\Controllers\Api\V1\EstimateController;
use App\Http\Controllers\Api\V1\ItemController;
use App\Http\Controllers\Api\V1\VendorController;
use App\Http\Middleware\EnsureTeamMembership;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/teams/{current_team}')
    ->as('api.v1.')
    ->middleware(['auth:sanctum', 'verified', EnsureTeamMembership::class])
    ->scopeBindings()
    ->group(function (): void {
        Route::apiResource('customers', CustomerController::class);
        Route::apiResource('vendors', VendorController::class);
        Route::apiResource('items', ItemController::class);
        Route::apiResource('estimates', EstimateController::class);
        Route::patch('estimates/{estimate}/status', [EstimateController::class, 'updateStatus'])
            ->name('estimates.status.update');
    });
