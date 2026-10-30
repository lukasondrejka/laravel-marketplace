<header class="bg-base-100 border-b border-base-300 sticky top-0 z-40">
    <div class="max-w-6xl mx-auto px-4 py-3 flex flex-wrap items-center gap-x-6 gap-y-3">
        <a href="{{ route('home') }}" wire:navigate class="text-lg font-semibold tracking-tight text-base-content">
            {{ config('app.name') }}
        </a>

        <form action="{{ route('listings.index') }}" method="GET" role="search" class="order-last w-full md:order-none md:w-auto md:flex-1 md:max-w-md">
            <label class="input input-bordered w-full flex items-center gap-2 bg-base-200">
                <x-icon name="magnifying-glass" class="size-4 text-base-content/50" />
                <input type="search" name="q" value="{{ request('q') }}" placeholder="{{ __('Search listings') }}" aria-label="{{ __('Search listings') }}" class="grow">
            </label>
        </form>

        <nav class="ml-auto flex items-center gap-1 sm:gap-2" aria-label="{{ __('Main') }}">
            <x-theme-toggle />

            @auth
                <a href="{{ route('listings.create') }}" wire:navigate class="btn btn-primary">{{ __('Post a listing') }}</a>

                <div class="dropdown dropdown-end">
                    <button type="button" tabindex="0" class="btn btn-ghost btn-circle" aria-label="{{ __('Account menu') }}">
                        <x-avatar :user="auth()->user()" class="size-8" />
                    </button>
                    <ul tabindex="0" class="menu dropdown-content bg-base-100 rounded-box z-50 mt-2 w-56 p-2 border border-base-300">
                        <li class="menu-title truncate normal-case">{{ auth()->user()->name }}</li>
                        <li><a href="{{ route('dashboard') }}" wire:navigate>{{ __('My listings') }}</a></li>
                        <li><a href="{{ route('profile.edit') }}" wire:navigate>{{ __('Profile') }}</a></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}" class="p-0">
                                @csrf
                                <button type="submit" class="w-full text-left px-3 py-1.5">{{ __('Log out') }}</button>
                            </form>
                        </li>
                    </ul>
                </div>
            @else
                <a href="{{ route('login') }}" class="btn btn-ghost">{{ __('Log in') }}</a>
                <a href="{{ route('listings.create') }}" wire:navigate class="btn btn-primary">{{ __('Post a listing') }}</a>
            @endauth
        </nav>
    </div>
</header>
