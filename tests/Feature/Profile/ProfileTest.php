<?php

use App\Livewire\Profile\DeleteAccount;
use App\Livewire\Profile\UpdateInformation;
use App\Livewire\Profile\UpdatePassword;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(fn () => $this->user = User::factory()->create());

it('shows the profile page', function () {
    $this->actingAs($this->user)->get('/profile')->assertOk()->assertSee($this->user->email);
});

it('updates profile information', function () {
    Livewire::actingAs($this->user)->test(UpdateInformation::class)
        ->set('form.name', 'New Name')
        ->set('form.phone', '+421 900 123 456')
        ->set('form.city', 'Kosice')
        ->call('save')
        ->assertHasNoErrors();

    expect($this->user->fresh())
        ->name->toBe('New Name')
        ->phone->toBe('+421 900 123 456')
        ->city->toBe('Kosice');
});

it('resets verification when the email changes', function () {
    Notification::fake();

    Livewire::actingAs($this->user)->test(UpdateInformation::class)
        ->set('form.email', 'changed@example.com')
        ->call('save')
        ->assertHasNoErrors();

    expect($this->user->fresh()->email)->toBe('changed@example.com')
        ->and($this->user->fresh()->hasVerifiedEmail())->toBeFalse();
});

it('rejects an email used by another user and invalid phone numbers', function () {
    $other = User::factory()->create();

    Livewire::actingAs($this->user)->test(UpdateInformation::class)
        ->set('form.email', $other->email)
        ->set('form.phone', 'abc')
        ->call('save')
        ->assertHasErrors(['form.email', 'form.phone']);
});

it('uploads an avatar and validates its type', function () {
    Storage::fake('public');

    Livewire::actingAs($this->user)->test(UpdateInformation::class)
        ->set('form.avatar', UploadedFile::fake()->create('doc.pdf', 10, 'application/pdf'))
        ->call('save')
        ->assertHasErrors('form.avatar');

    Livewire::actingAs($this->user)->test(UpdateInformation::class)
        ->set('form.avatar', UploadedFile::fake()->image('me.jpg', 300, 300))
        ->call('save')
        ->assertHasNoErrors();

    expect($this->user->fresh()->getFirstMedia('avatar'))->not->toBeNull();
});

it('changes the password only with the correct current password', function () {
    Livewire::actingAs($this->user)->test(UpdatePassword::class)
        ->set('current_password', 'wrong')
        ->set('password', 'brand-new-pass-1')
        ->set('password_confirmation', 'brand-new-pass-1')
        ->call('save')
        ->assertHasErrors('current_password');

    Livewire::actingAs($this->user)->test(UpdatePassword::class)
        ->set('current_password', 'password')
        ->set('password', 'brand-new-pass-1')
        ->set('password_confirmation', 'brand-new-pass-1')
        ->call('save')
        ->assertHasNoErrors();

    expect(Hash::check('brand-new-pass-1', $this->user->fresh()->password))->toBeTrue();
});

it('deletes the account only after password confirmation', function () {
    Livewire::actingAs($this->user)->test(DeleteAccount::class)
        ->set('password', 'wrong')
        ->call('delete')
        ->assertHasErrors('password');
    expect(User::find($this->user->id))->not->toBeNull();

    Livewire::actingAs($this->user)->test(DeleteAccount::class)
        ->set('password', 'password')
        ->call('delete')
        ->assertRedirect('/');
    expect(User::find($this->user->id))->toBeNull();
});

it('removes the avatar on request and replaces it on upload', function () {
    Storage::fake('public');

    Livewire::actingAs($this->user)->test(UpdateInformation::class)
        ->set('form.avatar', UploadedFile::fake()->image('one.jpg', 200, 200))->call('save');
    Livewire::actingAs($this->user->fresh())->test(UpdateInformation::class)
        ->set('form.avatar', UploadedFile::fake()->image('two.jpg', 200, 200))->call('save');

    expect($this->user->fresh()->getMedia('avatar'))->toHaveCount(1)
        ->and($this->user->fresh()->avatarUrl())->not->toBeNull();

    Livewire::actingAs($this->user->fresh())->test(UpdateInformation::class)->set('form.removeAvatar', true)->call('save');

    expect($this->user->fresh()->getMedia('avatar'))->toHaveCount(0)->and($this->user->fresh()->avatarUrl())->toBeNull();
});

it('rejects avatars that are too large or not allowed image types', function (string $name, int $kb) {
    Storage::fake('public');

    Livewire::actingAs($this->user)->test(UpdateInformation::class)
        ->set('form.avatar', UploadedFile::fake()->image($name)->size($kb))
        ->call('save')
        ->assertHasErrors('form.avatar');
})->with([
    'too big' => ['big.jpg', 3000],
    'svg' => ['logo.svg', 10],
    'gif' => ['anim.gif', 10],
]);

it('keeps verification when the email is unchanged and stores blank optional fields as null', function () {
    Notification::fake();

    Livewire::actingAs($this->user)->test(UpdateInformation::class)
        ->set('form.phone', '')->set('form.city', '')->set('form.bio', '')
        ->call('save')->assertHasNoErrors();

    $fresh = $this->user->fresh();
    expect($fresh->hasVerifiedEmail())->toBeTrue()->and($fresh->phone)->toBeNull()->and($fresh->city)->toBeNull()->and($fresh->bio)->toBeNull();
    Notification::assertNothingSent();
});

it('validates profile field lengths', function (string $field, string $value) {
    Livewire::actingAs($this->user)->test(UpdateInformation::class)
        ->set($field, $value)->call('save')->assertHasErrors($field);
})->with([
    'empty name' => ['form.name', ''],
    'long bio' => ['form.bio', str_repeat('x', 501)],
    'long city' => ['form.city', str_repeat('x', 101)],
    'long phone' => ['form.phone', str_repeat('1', 33)],
    'bad email' => ['form.email', 'not-an-email'],
]);

it('requires a new password that is confirmed and long enough', function () {
    Livewire::actingAs($this->user)->test(UpdatePassword::class)
        ->set('current_password', 'password')->set('password', 'short')->set('password_confirmation', 'short')
        ->call('save')->assertHasErrors('password');

    Livewire::actingAs($this->user)->test(UpdatePassword::class)
        ->set('current_password', 'password')->set('password', 'a-long-new-password')->set('password_confirmation', 'different')
        ->call('save')->assertHasErrors('password');
});

it('keeps the account when the delete password is wrong', function () {
    Livewire::actingAs($this->user)->test(DeleteAccount::class)->set('password', 'nope')->call('delete')->assertHasErrors('password');

    expect(User::find($this->user->id))->not->toBeNull();
});
