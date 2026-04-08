<?php

declare(strict_types=1);

use DataBridge\Auth\AuthController;
use DataBridge\Admin\DashboardController;
use DataBridge\Core\Router;

/** @var Router $router */

// ── Auth (public) ──────────────────────────────────────────────────────────
$router->get('/login',  [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'processLogin']);
$router->post('/logout',[AuthController::class, 'logout']);

// ── Dashboard ──────────────────────────────────────────────────────────────
$router->get('/',          [DashboardController::class, 'redirect']);
$router->get('/dashboard', [DashboardController::class, 'index']);

// ── Site Groups ────────────────────────────────────────────────────────────
use DataBridge\Admin\SiteGroupsController;
$router->get('/site-groups',           [SiteGroupsController::class, 'index']);
$router->get('/site-groups/{id}',      [SiteGroupsController::class, 'show']);
$router->post('/site-groups/create',   [SiteGroupsController::class, 'create']);
$router->post('/site-groups/update',   [SiteGroupsController::class, 'update']);
$router->post('/site-groups/delete',   [SiteGroupsController::class, 'delete']);

// ── Sites ──────────────────────────────────────────────────────────────────
use DataBridge\Admin\SitesController;
$router->get('/sites',            [SitesController::class, 'index']);
$router->get('/sites/{id}',       [SitesController::class, 'show']);
$router->post('/sites/create',    [SitesController::class, 'create']);
$router->post('/sites/update',    [SitesController::class, 'update']);
$router->post('/sites/delete',    [SitesController::class, 'delete']);
$router->post('/sites/toggle',    [SitesController::class, 'toggle']);

// ── Users ──────────────────────────────────────────────────────────────────
use DataBridge\Admin\UsersController;
$router->get('/users',               [UsersController::class, 'index']);
$router->post('/users/create',       [UsersController::class, 'create']);
$router->post('/users/update',       [UsersController::class, 'update']);
$router->post('/users/delete',       [UsersController::class, 'delete']);

// ── Logs ───────────────────────────────────────────────────────────────────
use DataBridge\Admin\LogsController;
$router->get('/logs', [LogsController::class, 'index']);

// ── API (Bearer auth, no CSRF) ─────────────────────────────────────────────
use DataBridge\Api\SyncController;
$router->get('/api/v1/health',          [SyncController::class, 'health']);
$router->get('/api/v1/sync',            [SyncController::class, 'pull']);
$router->get('/api/v1/sync/phones',     [SyncController::class, 'pullPhones']);
$router->get('/api/v1/sync/prices',     [SyncController::class, 'pullPrices']);
$router->get('/api/v1/sync/addresses',  [SyncController::class, 'pullAddresses']);
$router->get('/api/v1/sync/socials',    [SyncController::class, 'pullSocials']);
$router->get('/api/v1/sync/meta',       [SyncController::class, 'pullMeta']);
