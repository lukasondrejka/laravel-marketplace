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
