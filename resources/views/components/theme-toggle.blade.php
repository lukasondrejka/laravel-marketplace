<button type="button" class="btn btn-ghost btn-circle" x-data @click="$store.theme.toggle()" aria-label="{{ __('Toggle dark mode') }}">
    <x-icon name="moon" class="size-5" x-show="$store.theme.current !== 'dark'" />
    <x-icon name="sun" class="size-5" x-show="$store.theme.current === 'dark'" x-cloak />
</button>
