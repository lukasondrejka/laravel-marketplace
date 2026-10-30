@props(['user'])
<span {{ $attributes->class('inline-flex shrink-0 items-center justify-center rounded-full overflow-hidden bg-base-300 text-base-content/70 text-xs font-semibold') }}>
    @if ($user->avatarUrl())
        <img src="{{ $user->avatarUrl() }}" alt="" class="size-full object-cover">
    @else
        {{ $user->initials() }}
    @endif
</span>
