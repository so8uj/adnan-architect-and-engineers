<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\ContentController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\MessageController;
use App\Http\Controllers\Admin\PageSeoController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('login', [AuthController::class, 'showLogin'])->name('login')->middleware('guest');
    Route::post('login', [AuthController::class, 'login'])->middleware(['guest', 'throttle:10,1']);

    Route::middleware('auth')->group(function () {
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::post('cache/clear', [DashboardController::class, 'clearCache'])->name('cache.clear');

        Route::get('settings/{group}', [SettingsController::class, 'edit'])->name('settings.edit');
        Route::put('settings/{group}', [SettingsController::class, 'update'])->name('settings.update');

        Route::get('pages', [PageSeoController::class, 'index'])->name('pages.index');
        Route::get('pages/{page}', [PageSeoController::class, 'edit'])->name('pages.edit');
        Route::put('pages/{page}', [PageSeoController::class, 'update'])->name('pages.update');

        Route::get('profile', [ProfileController::class, 'edit'])->name('profile');
        Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::resource('users', UserController::class)->except(['show']);

        Route::get('messages', [MessageController::class, 'index'])->name('messages');
        Route::post('messages/{message}/read', [MessageController::class, 'read'])->name('messages.read');
        Route::delete('messages/{message}', [MessageController::class, 'destroy'])->name('messages.destroy');

        Route::prefix('c/{resource}')->group(function () {
            Route::get('/', [ContentController::class, 'index'])->name('content.index');
            Route::get('create', [ContentController::class, 'create'])->name('content.create');
            Route::post('/', [ContentController::class, 'store'])->name('content.store');
            Route::post('reorder', [ContentController::class, 'reorder'])->name('content.reorder');
            Route::get('{id}/edit', [ContentController::class, 'edit'])->name('content.edit');
            Route::put('{id}', [ContentController::class, 'update'])->name('content.update');
            Route::delete('{id}', [ContentController::class, 'destroy'])->name('content.destroy');
        });
    });
});
