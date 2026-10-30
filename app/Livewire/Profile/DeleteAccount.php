<?php

namespace App\Livewire\Profile;

use App\Actions\Users\DeleteAccount as DeleteAccountAction;
use Livewire\Component;

class DeleteAccount extends Component
{
    public string $password = '';

    public function delete(DeleteAccountAction $action)
    {
        $this->validate(['password' => ['required', 'current_password']]);

        $action->handle(auth()->user());

        return $this->redirect(route('home'), navigate: false);
    }

    public function render()
    {
        return view('livewire.profile.delete-account');
    }
}
