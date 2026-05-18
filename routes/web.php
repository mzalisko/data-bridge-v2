<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\SiteController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\SiteGroupController;
use App\Http\Controllers\Admin\FavoriteController;
use App\Http\Controllers\Admin\LogController;
use App\Http\Controllers\Admin\ApiKeyController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\SitePhoneController;
use App\Http\Controllers\Admin\SitePriceController;
use App\Http\Controllers\Admin\SiteAddressController;
use App\Http\Controllers\Admin\SiteSocialController;
use App\Http\Controllers\Admin\SiteGeoController;
use App\Http\Controllers\Admin\SiteCustomFieldController;
use App\Http\Controllers\Admin\BulkDataController;
use App\Http\Controllers\Admin\SiteFailoverController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\DataBrowserController;
use App\Http\Controllers\Admin\CustomPlatformController;
use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Route;

// Auth routes (guests only)
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLogin'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

Route::post('/logout', [LoginController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// Admin routes (auth required)
Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Reads — any authenticated user.
    Route::resource('site-groups', SiteGroupController::class)->only(['index', 'show']);
    Route::resource('sites', SiteController::class)->only(['index', 'show']);

    // Writes — require 'edit' permission (admin/manager bypass).
    Route::middleware('perm:edit')->group(function () {
        Route::resource('site-groups', SiteGroupController::class)->only(['store', 'update']);
        Route::resource('sites', SiteController::class)->only(['store', 'update']);

        Route::post('sites/{site}/favorite', [FavoriteController::class, 'toggle'])->name('sites.favorite');

        Route::post(  'sites/{site}/phones',              [SitePhoneController::class,   'store']  )->name('phones.store');
        Route::post(  'sites/{site}/phones/reorder',      [SitePhoneController::class,   'reorder'])->name('phones.reorder');
        Route::put(   'sites/{site}/phones/{phone}',      [SitePhoneController::class,   'update'] )->name('phones.update');

        Route::post(  'sites/{site}/prices',              [SitePriceController::class,   'store']  )->name('prices.store');
        Route::put(   'sites/{site}/prices/{price}',      [SitePriceController::class,   'update'] )->name('prices.update');

        Route::post(  'sites/{site}/addresses',           [SiteAddressController::class, 'store']  )->name('addresses.store');
        Route::put(   'sites/{site}/addresses/{address}', [SiteAddressController::class, 'update'] )->name('addresses.update');

        Route::post(  'sites/{site}/socials',             [SiteSocialController::class,  'store']  )->name('socials.store');
        Route::post(  'sites/{site}/socials/reorder',     [SiteSocialController::class,  'reorder'])->name('socials.reorder');
        Route::put(   'sites/{site}/socials/{social}',    [SiteSocialController::class,  'update'] )->name('socials.update');

        Route::post(  'sites/{site}/presence',           [SiteController::class,            'presence'])->name('sites.presence');

        Route::post(  'sites/{site}/fields',             [SiteCustomFieldController::class, 'store']  )->name('fields.store');
        Route::put(   'sites/{site}/fields/{field}',     [SiteCustomFieldController::class, 'update'] )->name('fields.update');

        // Geo management (site config — treated as edit, incl. geo removal).
        Route::post(  'sites/{site}/geos',                   [SiteGeoController::class, 'addGeo'])->name('sites.geos.add');
        Route::delete('sites/{site}/geos/{iso}',             [SiteGeoController::class, 'removeGeo'])->name('sites.geos.remove');
        Route::post(  'sites/{site}/geo-rules',              [SiteGeoController::class, 'saveRules'])->name('sites.geo-rules.save');
        Route::post(  'sites/{site}/visibility/{type}/{id}', [SiteGeoController::class, 'toggleVisibility'])->name('sites.visibility.toggle');
        Route::post(  'sites/{site}/activity/{log}/restore', [SiteController::class, 'restoreActivity'])->name('sites.activity.restore');
        Route::put(   'sites/{site}/push-settings',          [SiteController::class, 'updatePushSettings'])->name('sites.push-settings.update');
        Route::post(  'sites/{site}/sync',                   [SiteController::class, 'syncPush'])->name('sites.sync');

        Route::post(  'sites/{site}/failover/standby',        [SiteFailoverController::class, 'toggleStandby'])->name('sites.failover.standby');
        Route::post(  'sites/{site}/failover/link',           [SiteFailoverController::class, 'linkStandby'])->name('sites.failover.link');
        Route::post(  'sites/{site}/failover/trigger',        [SiteFailoverController::class, 'trigger'])->name('sites.failover.trigger');
        Route::post(  'sites/{site}/failover/cascade',        [SiteFailoverController::class, 'cascade'])->name('sites.failover.cascade');
        Route::post(  'sites/{site}/failover/restore',        [SiteFailoverController::class, 'restoreManual'])->name('sites.failover.restore');
        Route::post(  'sites/{site}/failover/{log}/rollback', [SiteFailoverController::class, 'rollback'])->name('sites.failover.rollback');
    });

    // Record deletion — require 'delete' permission.
    Route::middleware('perm:delete')->group(function () {
        Route::resource('site-groups', SiteGroupController::class)->only(['destroy']);
        Route::resource('sites', SiteController::class)->only(['destroy']);
        Route::delete('sites/{site}/phones/{phone}',      [SitePhoneController::class,   'destroy'])->name('phones.destroy');
        Route::delete('sites/{site}/prices/{price}',      [SitePriceController::class,   'destroy'])->name('prices.destroy');
        Route::delete('sites/{site}/addresses/{address}', [SiteAddressController::class, 'destroy'])->name('addresses.destroy');
        Route::delete('sites/{site}/socials/{social}',    [SiteSocialController::class,  'destroy'])->name('socials.destroy');
        Route::delete('sites/{site}/fields/{field}',      [SiteCustomFieldController::class, 'destroy'])->name('fields.destroy');
        Route::delete('sites/{site}/failover/history',    [SiteFailoverController::class, 'clearHistory'])->name('sites.failover.history.clear');
    });

    // API key issuance — require 'api_key' permission.
    Route::middleware('perm:api_key')->group(function () {
        Route::post('sites/{site}/api-key/generate', [ApiKeyController::class, 'generate'])->name('sites.api-key.generate');
        Route::post('sites/{site}/api-key/revoke',   [ApiKeyController::class, 'revoke'])->name('sites.api-key.revoke');
    });

    // Bulk data operations (multi-site) — admin-only (writes across many sites,
    // no per-site Policy layer yet; destructive on the plugin side via push).
    Route::middleware('admin')->group(function () {
        Route::post('bulk/phones',     [BulkDataController::class, 'addPhone'])->name('bulk.phones');
        Route::post('bulk/prices',     [BulkDataController::class, 'addPrice'])->name('bulk.prices');
        Route::post('bulk/addresses',  [BulkDataController::class, 'addAddress'])->name('bulk.addresses');
        Route::post('bulk/socials',    [BulkDataController::class, 'addSocial'])->name('bulk.socials');
        Route::post('bulk/geos',       [BulkDataController::class, 'addGeo'])->name('bulk.geos');
        Route::post('bulk/delete',     [BulkDataController::class, 'deleteMatching'])->name('bulk.delete');
    });

    // User & permission management — admin-only (account creation + RBAC grants
    // = privilege escalation surface; no per-action Policy layer yet).
    Route::middleware('admin')->group(function () {
        Route::resource('users', UserController::class)
            ->only(['index', 'store', 'update', 'destroy']);

        Route::get('users/{user}/permissions', [PermissionController::class, 'show'])->name('users.permissions.show');
        Route::get('users/{user}/permissions/form', [PermissionController::class, 'fragment'])->name('users.permissions.fragment');
        Route::post('users/{user}/permissions', [PermissionController::class, 'update'])->name('users.permissions.update');
    });

    Route::get('/logs/system',   [LogController::class, 'system'])->name('logs.system');
    Route::get('/logs/sync',     [LogController::class, 'sync'])->name('logs.sync');
    Route::get('/logs/activity', [LogController::class, 'activity'])->name('logs.activity');

    // Data Browser — read for everyone, destructive ops admin-only
    Route::post('custom-platforms', [CustomPlatformController::class, 'store'])
        ->middleware('perm:edit')->name('custom-platforms.store');
    Route::get( 'data',             [DataBrowserController::class, 'index'])->name('data.index');
    Route::middleware('admin')->group(function () {
        Route::post('data/bulk-delete', [DataBrowserController::class, 'bulkDelete'])->name('data.bulk-delete');
        Route::post('data/bulk-edit',   [DataBrowserController::class, 'bulkEdit'])->name('data.bulk-edit');
        Route::post('data/bulk-copy',   [DataBrowserController::class, 'bulkCopy'])->name('data.bulk-copy');
    });

    // Settings
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings/countries', [SettingsController::class, 'storeCountry'])->name('settings.countries.store');
    Route::delete('/settings/countries/{country}', [SettingsController::class, 'destroyCountry'])->name('settings.countries.destroy');
});
