<?php

namespace App\Livewire\Listings;

use App\Actions\Listings\SendInquiry;
use App\Enums\ListingStatus;
use App\Models\Listing;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;

class ContactSeller extends Component
{
    public Listing $listing;

    public string $name = '';

    public string $email = '';

    public string $message = '';

    public bool $sent = false;

    public function mount(Listing $listing): void
    {
        $this->listing = $listing;

        if ($user = auth()->user()) {
            $this->name = $user->name;
            $this->email = $user->email;
        }
    }

    public function send(SendInquiry $action): void
    {
        abort_if($this->listing->isOwnedBy(auth()->user()), 403);
        abort_unless($this->listing->status === ListingStatus::Active, 404);

        $key = 'inquiry:'.(auth()->id() ?? request()->ip());

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $this->addError('message', __('You have sent too many messages. Please try again later.'));

            return;
        }

        $data = $this->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255'],
            'message' => ['required', 'string', 'min:10', 'max:2000'],
        ]);

        RateLimiter::hit($key, 3600);

        $action->handle($this->listing, $data['name'], $data['email'], $data['message']);

        $this->reset('message');
        $this->sent = true;
    }

    public function render()
    {
        return view('livewire.listings.contact-seller');
    }
}
