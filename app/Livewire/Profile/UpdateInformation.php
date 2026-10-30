<?php

namespace App\Livewire\Profile;

use App\Actions\Users\UpdateProfile;
use App\Livewire\Forms\ProfileForm;
use Livewire\Component;
use Livewire\WithFileUploads;

class UpdateInformation extends Component
{
    use WithFileUploads;

    public ProfileForm $form;

    public function mount(): void
    {
        $this->form->fillFromUser(auth()->user());
    }

    public function save(UpdateProfile $updateProfile): void
    {
        $data = $this->form->validate();

        $user = $updateProfile->handle(auth()->user(), $data, $this->form->avatar, $this->form->removeAvatar);

        $this->form->reset('avatar', 'removeAvatar');
        $this->form->fillFromUser($user);

        $this->dispatch('notify', type: 'success', message: __('Profile updated.'));

        if (! $user->hasVerifiedEmail()) {
            $this->dispatch('notify', type: 'info', message: __('Please verify your new email address.'));
        }
    }

    public function render()
    {
        return view('livewire.profile.update-information');
    }
}
