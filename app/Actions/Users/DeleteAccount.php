<?php

namespace App\Actions\Users;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

class DeleteAccount
{
    public function handle(User $user): void
    {
        // Listings are force-deleted one by one so their media files are removed too.
        $user->listings()->withTrashed()->each(fn ($listing) => $listing->forceDelete());

        $user->clearMediaCollection('avatar');

        Auth::logout();
        $user->delete();

        session()->invalidate();
        session()->regenerateToken();
    }
}
