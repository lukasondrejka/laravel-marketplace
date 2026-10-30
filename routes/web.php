<?php

use Illuminate\Support\Facades\Route;

// Temporary placeholders, replaced in later phases.
Route::view('/', 'home')->name('home');
Route::view('/listings', 'home')->name('listings.index');
Route::view('/listings/create', 'home')->name('listings.create');
Route::view('/dashboard', 'home')->name('dashboard');
Route::view('/profile', 'home')->name('profile.edit');
Route::view('/login', 'home')->name('login');
Route::view('/register', 'home')->name('register');
Route::post('/logout', fn () => redirect('/'))->name('logout');
