<?php

use App\Livewire\Profile\Edit as ProfileEdit;
use Illuminate\Support\Facades\Route;

// Temporary placeholders, replaced in later phases.
Route::view('/', 'home')->name('home');
Route::view('/listings', 'home')->name('listings.index');
Route::view('/listings/create', 'home')->middleware(['auth', 'verified'])->name('listings.create');
Route::view('/dashboard', 'home')->middleware('auth')->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::livewire('/profile', ProfileEdit::class)->name('profile.edit');
});
