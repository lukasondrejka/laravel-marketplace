<?php

namespace App\Actions\Users;

use App\Models\User;

class UpdatePassword
{
    public function handle(User $user, string $newPassword): void
    {
        $user->forceFill(['password' => $newPassword])->save();
    }
}
