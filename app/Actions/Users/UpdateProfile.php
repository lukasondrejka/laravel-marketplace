<?php

namespace App\Actions\Users;

use App\Models\User;
use Illuminate\Http\UploadedFile;

class UpdateProfile
{
    /**
     * @param  array{name: string, email: string, phone?: ?string, city?: ?string, bio?: ?string}  $data
     */
    public function handle(User $user, array $data, ?UploadedFile $avatar = null, bool $removeAvatar = false): User
    {
        $user->fill([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?: null,
            'city' => $data['city'] ?: null,
            'bio' => $data['bio'] ?: null,
        ]);

        $emailChanged = $user->isDirty('email');

        if ($emailChanged) {
            $user->email_verified_at = null;
        }

        $user->save();

        if ($avatar) {
            $user->addMedia($avatar->getRealPath())
                ->usingFileName($avatar->hashName())
                ->toMediaCollection('avatar');
        } elseif ($removeAvatar) {
            $user->clearMediaCollection('avatar');
        }

        if ($emailChanged) {
            $user->sendEmailVerificationNotification();
        }

        return $user;
    }
}
