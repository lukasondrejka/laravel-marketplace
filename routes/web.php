<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\ProfileController;
use App\Http\Middleware\Authenticate;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Homepage
Route::get('/', [HomeController::class, 'index'])
    ->name('home');


// Item
Route::get('/items', [ItemController::class, 'index'])
    ->name('items.items');

Route::middleware('auth')->group(function () {
    Route::get('/item/create', [ItemController::class, 'create'])
        ->name('items.create');

    Route::post('/item/create', [ItemController::class, 'store'])
        ->name('items.create');

    Route::get('/item/{id}/edit', [ItemController::class, 'edit'])
        ->name('items.edit');

    Route::post('/item/{id}/edit', [ItemController::class, 'update'])
        ->name('items.update');

    Route::post('/item/{id}/delete', [ItemController::class, 'destroy'])
        ->name('items.destroy');
});

Route::get('item/{id}', [ItemController::class, 'show'])
    ->name('items.show');

// User
Route::get('/user/{id}', [ProfileController::class, 'show'])
    ->name('profile.show');

// User Profile
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');
});

// Auth
require __DIR__.'/auth.php';
