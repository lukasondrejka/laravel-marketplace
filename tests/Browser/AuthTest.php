<?php

use App\Models\User;

it('registers a new account and lands on the dashboard', function () {
    $page = visit('/register')
        ->inLightMode()
        ->fill('name', 'Jane Seller')
        ->fill('email', 'jane@example.com')
        ->fill('password', 'a-very-secret-pass')
        ->fill('password_confirmation', 'a-very-secret-pass')
        ->submit();

    e2eWait($page, "location.pathname === '/dashboard'", 'redirect to the dashboard');
    $page->assertSee('My listings')->assertSee('Verify your email address to post listings.')->assertNoJavaScriptErrors();

    expect(User::query()->where('email', 'jane@example.com')->exists())->toBeTrue();
});

it('logs in with valid credentials and logs out again', function () {
    $user = User::factory()->create(['email' => 'seller@example.com']);

    $page = visit('/login')
        ->fill('email', $user->email)
        ->fill('password', 'password')
        ->submit();

    e2eWait($page, "location.pathname === '/dashboard'", 'redirect to the dashboard');
    $page->assertSee('My listings');

    // The logout button lives in the account dropdown, which opens on focus/click.
    $page->click('[aria-label="Account menu"]')->click('Log out');
    e2eWait($page, "document.querySelector('a[href$=\"/login\"]') !== null", 'guest navigation after logout');
});

it('shows an error for a wrong password', function () {
    $user = User::factory()->create();

    $page = visit('/login')
        ->fill('email', $user->email)
        ->fill('password', 'not-the-password')
        ->submit();

    e2eWait($page, "document.body.innerText.includes('These credentials do not match our records.')", 'credentials error');
    $page->assertPathIs('/login');
});
