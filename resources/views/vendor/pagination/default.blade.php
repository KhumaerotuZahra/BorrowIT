@if ($paginator->hasPages())
    <nav class="custom-pagination">
        {{-- Previous --}}
        @if ($paginator->onFirstPage())
            <span class="pg-arrow disabled">&lsaquo;</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" class="pg-arrow">&lsaquo;</a>
        @endif

        {{-- Page Numbers --}}
        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="pg-dots">{{ $element }}</span>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="pg-num active">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" class="pg-num">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="pg-arrow">&rsaquo;</a>
        @else
            <span class="pg-arrow disabled">&rsaquo;</span>
        @endif
    </nav>
@endif
