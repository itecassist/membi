<?php

use App\Http\Controllers\Api\Admin\OrganisationSuperAdminController;
use App\Http\Controllers\Api\Admin\PermissionController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\GroupController;
use App\Http\Controllers\Api\MemberController;
use App\Http\Controllers\Api\OrganisationController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\SubscriptionController;
use Illuminate\Support\Facades\Route;

// ── Auth ─────────────────────────────────────────────────────────────────────
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login',    [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('me',           [AuthController::class, 'me']);
        Route::get('organisations', [AuthController::class, 'organisations']);
        Route::post('logout',      [AuthController::class, 'logout']);
        Route::post('logout-all',  [AuthController::class, 'logoutAll']);
    });
});

// ── Membix Admin ─────────────────────────────────────────────────────────────
Route::middleware(['auth:sanctum', 'membix.admin'])->prefix('admin')->group(function () {

    // Permission management (Membix admins only)
    Route::apiResource('permissions', PermissionController::class);
    Route::patch('permissions/{permission}/toggle-visibility', [PermissionController::class, 'toggleVisibility']);

    // super-admin assignment per organisation (Membix admins only)
    Route::get('organisations/{organisation}/super-admins',              [OrganisationSuperAdminController::class, 'index']);
    Route::post('organisations/{organisation}/super-admins',             [OrganisationSuperAdminController::class, 'store']);
    Route::delete('organisations/{organisation}/super-admins/{member}',  [OrganisationSuperAdminController::class, 'destroy']);
});

// ── Protected API v1 ─────────────────────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    // Organisations (no org.team/org.member needed — no {organisation} param here)
    Route::apiResource('organisations', OrganisationController::class);

    // ── Org-scoped routes (team context + member resolved automatically) ────────
    Route::middleware(['org.team', 'org.member'])
        ->prefix('organisations/{organisation}')
        ->group(function () {

            // Permissions visible to this organisation (for building custom roles)
            Route::get('available-permissions', function () {
                $permissions = \Spatie\Permission\Models\Permission::where('visible_to_organisations', true)
                    ->orderBy('name')
                    ->get();

                return \App\Http\Resources\PermissionResource::collection($permissions);
            });

            // Roles
            Route::get('roles',                              [RoleController::class, 'index']);
            Route::post('roles',                             [RoleController::class, 'store']);
            Route::get('roles/{role}',                       [RoleController::class, 'show']);
            Route::put('roles/{role}',                       [RoleController::class, 'update']);
            Route::delete('roles/{role}',                    [RoleController::class, 'destroy']);
            Route::put('roles/{role}/permissions',           [RoleController::class, 'syncPermissions']);
            Route::post('roles/{role}/members',              [RoleController::class, 'assignToMember']);
            Route::delete('roles/{role}/members',            [RoleController::class, 'removeFromMember']);

            // Members
            Route::get('members',                            [MemberController::class, 'index']);
            Route::post('members',                           [MemberController::class, 'store']);
            Route::get('members/{member}',                   [MemberController::class, 'show']);
            Route::put('members/{member}',                   [MemberController::class, 'update']);
            Route::delete('members/{member}',                [MemberController::class, 'destroy']);

            // Groups
            Route::get('groups',                             [GroupController::class, 'index']);
            Route::post('groups',                            [GroupController::class, 'store']);
            Route::get('groups/{group}',                     [GroupController::class, 'show']);
            Route::put('groups/{group}',                     [GroupController::class, 'update']);
            Route::delete('groups/{group}',                  [GroupController::class, 'destroy']);
            Route::get('groups/{group}/members',             [GroupController::class, 'members']);
            Route::post('groups/{group}/members',            [GroupController::class, 'addMember']);
            Route::delete('groups/{group}/members/{memberId}', [GroupController::class, 'removeMember']);

            // Subscriptions + price options
            Route::get('subscriptions',                      [SubscriptionController::class, 'index']);
            Route::post('subscriptions',                     [SubscriptionController::class, 'store']);
            Route::get('subscriptions/{subscription}',       [SubscriptionController::class, 'show']);
            Route::put('subscriptions/{subscription}',       [SubscriptionController::class, 'update']);
            Route::delete('subscriptions/{subscription}',    [SubscriptionController::class, 'destroy']);
            Route::get('subscriptions/{subscription}/price-options',              [SubscriptionController::class, 'priceOptions']);
            Route::post('subscriptions/{subscription}/price-options',             [SubscriptionController::class, 'storePriceOption']);
            Route::put('subscriptions/{subscription}/price-options/{priceOption}',    [SubscriptionController::class, 'updatePriceOption']);
            Route::delete('subscriptions/{subscription}/price-options/{priceOption}', [SubscriptionController::class, 'destroyPriceOption']);
        });
});


