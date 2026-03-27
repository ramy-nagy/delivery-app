<?php

use App\Http\Controllers\Admin\CrudController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LoginController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->group(function (): void {
    Route::get('/login', [LoginController::class, 'showLogin'])->name('admin.login');
    Route::post('/login', [LoginController::class, 'login'])->name('admin.login.submit');
    Route::post('/logout', [LoginController::class, 'logout'])->name('admin.logout');

    Route::middleware('auth')->group(function (): void {
        Route::get('/', [DashboardController::class, 'index'])->name('admin.dashboard');

        Route::get('/restaurants', [CrudController::class, 'resource'])
            ->defaults('resource', 'restaurants')
            ->name('admin.restaurants');

        Route::get('/shops', [CrudController::class, 'resource'])
            ->defaults('resource', 'shops')
            ->name('admin.shops');
    });
});
