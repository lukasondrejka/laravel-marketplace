<?php

namespace App\Livewire\Profile;

use App\Actions\Users\UpdatePassword as UpdatePasswordAction;
use Illuminate\Validation\Rules\Password;
use Livewire\Component;

class UpdatePassword extends Component
{
    public string $current_password = '';

    public string $password = '';

    public string $password_confirmation = '';

    public function save(UpdatePasswordAction $action): void
    {
        $this->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::default()],
        ]);

        $action->handle(auth()->user(), $this->password);

        $this->reset('current_password', 'password', 'password_confirmation');
        $this->dispatch('notify', type: 'success', message: __('Password updated.'));
    }

    public function render()
    {
        return view('livewire.profile.update-password');
    }
}
