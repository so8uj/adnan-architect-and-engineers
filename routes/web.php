<?php

use App\Http\Controllers\SiteController;
use Illuminate\Support\Facades\Route;

Route::get('/', [SiteController::class, 'home'])->name('home');
Route::get('/portfolio', [SiteController::class, 'portfolio'])->name('portfolio');
Route::get('/portfolio/{status}', [SiteController::class, 'portfolio'])->where('status', 'completed|running')->name('portfolio.status');
Route::get('/project/{slug}', [SiteController::class, 'project'])->name('project');
Route::get('/insights', [SiteController::class, 'insights'])->name('insights');
Route::get('/insight/{slug}', [SiteController::class, 'insight'])->name('insight');
Route::post('/contact', [SiteController::class, 'contact'])->middleware('throttle:6,1')->name('contact');
Route::get('/sitemap.xml', [SiteController::class, 'sitemap']);
Route::get('/robots.txt', [SiteController::class, 'robots']);

require __DIR__.'/admin.php';
