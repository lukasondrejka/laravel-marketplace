<?php

namespace App\Actions\Users;

use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Socialite\AbstractUser;
use Laravel\Socialite\Contracts\User as ProviderUser;

class LoginWithProvider
{
    /**
     * Find or create the local user for an OAuth identity.
     *
     * An existing account is only linked by email when that account has a verified email,
     * so nobody can take over an address they do not own. An address the provider itself flags
     * as unverified is ignored completely.
     */
    public function handle(string $provider, ProviderUser $identity): User
    {
        $account = SocialAccount::with('user')
            ->where('provider', $provider)
            ->where('provider_id', $identity->getId())
            ->first();

        if ($account) {
            return $account->user;
        }

        return DB::transaction(function () use ($provider, $identity) {
            $email = $this->verifiedEmail($identity);
            $user = $email ? User::where('email', $email)->first() : null;

            if ($user && ! $user->hasVerifiedEmail()) {
                $user = null;
                $email = null;
            }

            if (! $user) {
                $user = User::create([
                    'name' => $identity->getName() ?: Str::before((string) $email, '@') ?: 'User',
                    // Placeholder address when the provider returns none; verified-only posting still applies.
                    'email' => $email ?: Str::lower($provider.'-'.$identity->getId()).'@users.invalid',
                    'password' => Str::random(40),
                ]);

                if ($email) {
                    $user->forceFill(['email_verified_at' => now()])->save();
                }
            }

            $user->socialAccounts()->create(['provider' => $provider, 'provider_id' => $identity->getId()]);

            return $user;
        });
    }

    private function verifiedEmail(ProviderUser $identity): ?string
    {
        $raw = $identity instanceof AbstractUser ? $identity->getRaw() : [];

        if (filter_var($raw['email_verified'] ?? true, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) === false) {
            return null;
        }

        return $identity->getEmail() ?: null;
    }
}
