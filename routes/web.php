<?php

use App\Http\Controllers\AdminPostController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\NewsController;
use Illuminate\Support\Facades\Route;

Route::get('/', [NewsController::class, 'home'])->name('home');
Route::get('/noticias', [NewsController::class, 'index'])->name('news.index');
Route::get('/categorias', [NewsController::class, 'categories'])->name('categories');
Route::get('/noticias/{post:slug}', [NewsController::class, 'show'])->name('news.show');
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->middleware('throttle:6,1')->name('login.store');
});
Route::post('/logout', [AuthController::class, 'destroy'])->middleware('auth')->name('logout');
Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminPostController::class, 'dashboard'])->name('dashboard');
    Route::resource('noticias', AdminPostController::class)->parameters(['noticias' => 'post'])->except('show');
});
