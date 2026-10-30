<?php

use App\Actions\Users\DeleteAccount;
use App\Models\SocialAccount;
use App\Models\User;
use Laravel\Socialite\Contracts\Factory;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Two\User as ProviderUser;

function fakeProvider(string $id, ?string $email, string $name = 'Social Person'): void
{
    fakeIdentity((new ProviderUser)->map(['id' => $id, 'name' => $name, 'email' => $email]));
}

function fakeIdentity(ProviderUser $identity): void
{
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

it('ignores an email the provider reports as unverified', function () {
    $existing = User::factory()->create(['email' => 'owner@example.com']);
    $identity = (new ProviderUser)->map(['id' => 'g-5', 'name' => 'Impostor', 'email' => 'owner@example.com'])
        ->setRaw(['email_verified' => false]);
    fakeIdentity($identity);

    $this->get('/auth/google/callback');

    expect(auth()->id())->not->toBe($existing->id)
        ->and($existing->socialAccounts()->count())->toBe(0)
        ->and(auth()->user()->hasVerifiedEmail())->toBeFalse();
});

it('creates an unverified placeholder account when the provider shares no email', function () {
    fakeProvider('g-6', null, 'No Email');

    $this->get('/auth/google/callback')->assertRedirect(route('dashboard'));

    $user = auth()->user();
    expect($user->email)->toEndWith('@users.invalid')
        ->and($user->hasVerifiedEmail())->toBeFalse()
        ->and($user->name)->toBe('No Email');
});

it('redirects to the login page with an error when the provider fails', function () {
    $provider = Mockery::mock(Provider::class);
    $provider->shouldReceive('user')->andThrow(new RuntimeException('invalid state'));
    $factory = Mockery::mock(Factory::class);
    $factory->shouldReceive('driver')->with('google')->andReturn($provider);
    app()->instance(Factory::class, $factory);

    $this->get('/auth/google/callback')->assertRedirect(route('login'))->assertSessionHas('error');
    $this->assertGuest();
});

it('redirects to the provider when enabled', function () {
    $provider = Mockery::mock(Provider::class);
    $provider->shouldReceive('redirect')->andReturn(redirect('https://accounts.example/auth'));
    $factory = Mockery::mock(Factory::class);
    $factory->shouldReceive('driver')->with('google')->andReturn($provider);
    app()->instance(Factory::class, $factory);

    $this->get('/auth/google/redirect')->assertRedirect('https://accounts.example/auth');
});

it('keeps logged in users away from the social endpoints', function () {
    $this->actingAs(User::factory()->create())->get('/auth/google/redirect')->assertRedirect();
});

it('rate limits the social endpoints', function () {
    foreach (range(1, 10) as $i) {
        $this->get('/auth/github/redirect')->assertNotFound();
    }

    $this->get('/auth/github/redirect')->assertStatus(429);
});

it('deletes social links together with the account', function () {
    fakeProvider('g-7', 'gone@example.com');
    $this->get('/auth/google/callback');
    $user = auth()->user();

    app(DeleteAccount::class)->handle($user);

    expect(SocialAccount::count())->toBe(0);
});
