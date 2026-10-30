<?php

use App\Models\User;
use Laravel\Socialite\Contracts\Factory;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Two\User as ProviderUser;

function fakeProvider(string $id, ?string $email, string $name = 'Social Person'): void
{
    $identity = (new ProviderUser)->map(['id' => $id, 'name' => $name, 'email' => $email]);

    $provider = Mockery::mock(Provider::class);
    $provider->shouldReceive('user')->andReturn($identity);

    $factory = Mockery::mock(Factory::class);
    $factory->shouldReceive('driver')->with('google')->andReturn($provider);
    app()->instance(Factory::class, $factory);
}

beforeEach(fn () => config(['services.google.client_id' => 'id', 'services.google.client_secret' => 'secret']));

it('returns 404 for disabled or unknown providers', function () {
    config(['services.google.client_id' => null]);

    $this->get('/auth/google/redirect')->assertNotFound();
    $this->get('/auth/github/redirect')->assertNotFound();
});

it('creates a verified user on first login', function () {
    fakeProvider('g-1', 'new@example.com');

    $this->get('/auth/google/callback')->assertRedirect(route('dashboard'));

    $user = User::firstWhere('email', 'new@example.com');
    expect($user)->not->toBeNull()->and($user->hasVerifiedEmail())->toBeTrue()
        ->and($user->socialAccounts)->toHaveCount(1);
    $this->assertAuthenticatedAs($user);
});

it('links to an existing account only when its email is verified', function () {
    $existing = User::factory()->create(['email' => 'me@example.com']);
    fakeProvider('g-2', 'me@example.com');

    $this->get('/auth/google/callback');

    $this->assertAuthenticatedAs($existing);
    expect($existing->socialAccounts()->count())->toBe(1);
});

it('does not take over an unverified account', function () {
    $victim = User::factory()->unverified()->create(['email' => 'victim@example.com']);
    fakeProvider('g-3', 'victim@example.com');

    $this->get('/auth/google/callback');

    expect(auth()->id())->not->toBe($victim->id)
        ->and($victim->socialAccounts()->count())->toBe(0);
});

it('reuses the linked account on later logins', function () {
    fakeProvider('g-4', 'again@example.com');
    $this->get('/auth/google/callback');
    auth()->logout();

    fakeProvider('g-4', 'again@example.com');
    $this->get('/auth/google/callback');

    expect(User::where('email', 'again@example.com')->count())->toBe(1);
});
