<?php

use App\Http\Controllers\Auth\SocialiteController;
use App\Livewire\Home;
use App\Livewire\Listings\Dashboard;
use App\Livewire\Listings\Form as ListingFormPage;
use App\Livewire\Listings\Index as ListingIndex;
use App\Livewire\Listings\Show as ListingShow;
use App\Livewire\Profile\Edit as ProfileEdit;
use App\Livewire\Sellers\Show as SellerShow;
use Illuminate\Support\Facades\Route;

Route::livewire('/', Home::class)->name('home');

Route::livewire('/listings', ListingIndex::class)->name('listings.index');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::livewire('/listings/create', ListingFormPage::class)->name('listings.create');
    Route::livewire('/listings/{listing:slug}/edit', ListingFormPage::class)->name('listings.edit');
});

Route::livewire('/listings/{listing:slug}', ListingShow::class)->name('listings.show');

Route::livewire('/sellers/{seller}', SellerShow::class)->name('sellers.show');

Route::middleware('guest')->group(function () {
    Route::get('/auth/{provider}/redirect', [SocialiteController::class, 'redirect'])->name('social.redirect');
    Route::get('/auth/{provider}/callback', [SocialiteController::class, 'callback'])->name('social.callback');
});

Route::middleware('auth')->group(function () {
    Route::livewire('/dashboard', Dashboard::class)->name('dashboard');
    Route::livewire('/profile', ProfileEdit::class)->name('profile.edit');
});
