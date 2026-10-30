<?php

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;

it('renders auth pages for guests', function (string $uri) {
    $this->get($uri)->assertOk();
})->with(['/login', '/register', '/forgot-password']);

it('registers a user and sends a verification email', function () {
    Notification::fake();

    $this->post('/register', [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'password' => 'password-123',
        'password_confirmation' => 'password-123',
    ])->assertRedirect('/dashboard');

    $user = User::firstWhere('email', 'jane@example.com');
    expect($user)->not->toBeNull()
        ->and($user->hasVerifiedEmail())->toBeFalse();
    $this->assertAuthenticatedAs($user);
    Notification::assertSentTo($user, VerifyEmail::class);
});

it('validates registration input', function () {
    User::factory()->create(['email' => 'taken@example.com']);

    $this->post('/register', ['name' => '', 'email' => 'taken@example.com', 'password' => 'x'])
        ->assertSessionHasErrors(['name', 'email', 'password']);
});

it('logs in with valid credentials and rejects invalid ones', function () {
    $user = User::factory()->create();

    $this->post('/login', ['email' => $user->email, 'password' => 'wrong'])->assertSessionHasErrors('email');
    $this->assertGuest();

    $this->post('/login', ['email' => $user->email, 'password' => 'password'])->assertRedirect('/dashboard');
    $this->assertAuthenticatedAs($user);
});

it('throttles repeated failed logins', function () {
    $user = User::factory()->create();

    foreach (range(1, 5) as $i) {
        $this->post('/login', ['email' => $user->email, 'password' => 'wrong']);
    }

    $this->post('/login', ['email' => $user->email, 'password' => 'password'])->assertStatus(429);
});

it('logs out', function () {
    $this->actingAs(User::factory()->create())->post('/logout')->assertRedirect('/');
    $this->assertGuest();
});

it('sends a password reset link and resets the password', function () {
    Notification::fake();
    $user = User::factory()->create();

    $this->post('/forgot-password', ['email' => $user->email])->assertSessionHas('status');

    Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
        $this->post('/reset-password', [
            'token' => $notification->token,
            'email' => $user->email,
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ])->assertRedirect('/login');

        return true;
    });

    expect(Hash::check('new-password-123', $user->fresh()->password))->toBeTrue();
});

it('verifies email via signed link', function () {
    $user = User::factory()->unverified()->create();

    $url = URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), [
        'id' => $user->id,
        'hash' => sha1($user->email),
    ]);

    $this->actingAs($user)->get($url)->assertRedirect();
    expect($user->fresh()->hasVerifiedEmail())->toBeTrue();
});

it('blocks unverified users from posting', function () {
    $this->actingAs(User::factory()->unverified()->create())
        ->get('/listings/create')
        ->assertRedirect('/email/verify');
});

it('redirects guests away from protected pages', function () {
    $this->get('/listings/create')->assertRedirect('/login');
    $this->get('/profile')->assertRedirect('/login');
});
