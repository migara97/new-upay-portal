<?php


use App\Enums\PermissionsEnum;
use App\Livewire\Backend\Dashboard;
use Illuminate\Support\Facades\Route;

Route::get('dashboard', Dashboard::class)->name('dashboard');

//portal user management
Route::group(['prefix' => 'user-management', 'as' => 'user-management.'], function () {
    Route::get('/users', \App\Livewire\Backend\UserManagement\UserManagement::class)->name('users')->can(PermissionsEnum::VIEW_USER_MANAGEMENT->value);
    Route::get('/roles', \App\Livewire\Backend\UserManagement\RoleManagement::class)->name('roles')->can(PermissionsEnum::VIEW_ROLE_MANAGEMENT->value);
});

//portal management
Route::group(['prefix' => 'portal-management', 'as' => 'portal-management.'],function () {
    Route::get('/activity-logs', \App\Livewire\Backend\PortalManagement\ActivituLog::class)->name('activity-logs')->can(PermissionsEnum::VIEW_ACTIVITY_LOGS->value);
});


// Providers management routes
Route::group(['prefix' => 'providers', 'as' => 'providers.'], function () {
    Route::get('biller', App\Livewire\Backend\BillerManagement\BillerManagement::class)->name('biller');
    Route::get('category', App\Livewire\Backend\BillerManagement\BillerCategoryManagement::class)->name('category');
});

