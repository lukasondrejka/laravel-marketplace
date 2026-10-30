<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Users\LoginWithProvider;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirect;

class SocialiteController extends Controller
{
    public const PROVIDERS = ['google', 'facebook'];

    public function redirect(string $provider): SymfonyRedirect
    {
        $this->ensureEnabled($provider);

        return Socialite::driver($provider)->redirect();
    }

    public function callback(string $provider, LoginWithProvider $login): RedirectResponse
    {
        $this->ensureEnabled($provider);

        try {
            $identity = Socialite::driver($provider)->user();
        } catch (\Throwable) {
            return redirect()->route('login')->with('error', __('Login with :provider failed. Please try again.', ['provider' => ucfirst($provider)]));
        }

        Auth::login($login->handle($provider, $identity), remember: true);
        request()->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    private function ensureEnabled(string $provider): void
    {
        abort_unless(in_array($provider, self::PROVIDERS, true) && config("services.$provider.client_id"), 404);
    }
}
