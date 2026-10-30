@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Page navigation') }}" class="flex flex-col items-center gap-2 mt-8">
        <div class="join">
            @if ($paginator->onFirstPage())
                <span class="join-item btn btn-disabled" aria-disabled="true" aria-label="{{ __('Previous') }}">&laquo;</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" wire:navigate class="join-item btn" rel="prev" aria-label="{{ __('Previous') }}">&laquo;</a>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="join-item btn btn-disabled">{{ $element }}</span>
                @endif
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="join-item btn btn-primary" aria-current="page">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" wire:navigate class="join-item btn" aria-label="{{ __('Go to page :page', ['page' => $page]) }}">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" wire:navigate class="join-item btn" rel="next" aria-label="{{ __('Next') }}">&raquo;</a>
            @else
                <span class="join-item btn btn-disabled" aria-disabled="true" aria-label="{{ __('Next') }}">&raquo;</span>
            @endif
        </div>
        <p class="text-sm text-base-content/60">
            {{ __('Showing :from to :to of :total results', ['from' => $paginator->firstItem(), 'to' => $paginator->lastItem(), 'total' => $paginator->total()]) }}
        </p>
    </nav>
@endif
