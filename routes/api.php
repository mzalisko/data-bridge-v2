<?php

use App\Http\Controllers\Api\SyncController;
use App\Http\Controllers\Api\ApiPhoneController;
use App\Http\Controllers\Api\ApiPriceController;
use App\Http\Controllers\Api\ApiAddressController;
use App\Http\Controllers\Api\ApiSocialController;
use App\Http\Controllers\Api\ApiCustomFieldController;
use Illuminate\Support\Facades\Route;

// Public health check (no auth required)
Route::get('/v1/health', [SyncController::class, 'health'])->name('api.health');

// Authenticated API endpoints
Route::middleware(['api.key', 'throttle:60,1'])->prefix('v1')->group(function () {

    // Pull (read) endpoints
    Route::get('/sync/status',    [SyncController::class, 'status']);
    Route::get('/sync',           [SyncController::class, 'pull']);
    Route::get('/sync/phones',    [SyncController::class, 'pullPhones']);
    Route::get('/sync/prices',    [SyncController::class, 'pullPrices']);
    Route::get('/sync/addresses', [SyncController::class, 'pullAddresses']);
    Route::get('/sync/socials',        [SyncController::class, 'pullSocials']);
    Route::get('/sync/custom-fields',  [SyncController::class, 'pullCustomFields']);

    // Write endpoints — phones
    Route::post('/phones',        [ApiPhoneController::class, 'store']);
    Route::put('/phones/{id}',    [ApiPhoneController::class, 'update']);
    Route::delete('/phones/{id}', [ApiPhoneController::class, 'destroy']);

    // Write endpoints — prices
    Route::post('/prices',        [ApiPriceController::class, 'store']);
    Route::put('/prices/{id}',    [ApiPriceController::class, 'update']);
    Route::delete('/prices/{id}', [ApiPriceController::class, 'destroy']);

    // Write endpoints — addresses
    Route::post('/addresses',        [ApiAddressController::class, 'store']);
    Route::put('/addresses/{id}',    [ApiAddressController::class, 'update']);
    Route::delete('/addresses/{id}', [ApiAddressController::class, 'destroy']);

    // Write endpoints — socials
    Route::post('/socials',        [ApiSocialController::class, 'store']);
    Route::put('/socials/{id}',    [ApiSocialController::class, 'update']);
    Route::delete('/socials/{id}', [ApiSocialController::class, 'destroy']);

    // Write endpoints — custom fields
    Route::post('/custom-fields',        [ApiCustomFieldController::class, 'store']);
    Route::put('/custom-fields/{id}',    [ApiCustomFieldController::class, 'update']);
    Route::delete('/custom-fields/{id}', [ApiCustomFieldController::class, 'destroy']);
});
