@if ($paginator->hasPages())
    <nav class="dash-pagination" role="navigation" aria-label="{{ __('Pagination Navigation') }}">
        <p class="dash-pagination-meta">
            Showing
            <span>{{ $paginator->firstItem() }}</span>
            to
            <span>{{ $paginator->lastItem() }}</span>
            of
            <span>{{ $paginator->total() }}</span>
        </p>
        <ul class="dash-pagination-list">
            @if ($paginator->onFirstPage())
                <li>
                    <span class="dash-pagination-link is-disabled" aria-disabled="true" aria-label="{{ __('pagination.previous') }}">&lsaquo;</span>
                </li>
            @else
                <li>
                    <a class="dash-pagination-link" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="{{ __('pagination.previous') }}">&lsaquo;</a>
                </li>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <li>
                        <span class="dash-pagination-link is-disabled" aria-disabled="true">{{ $element }}</span>
                    </li>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        <li>
                            @if ($page == $paginator->currentPage())
                                <span class="dash-pagination-link is-current" aria-current="page">{{ $page }}</span>
                            @else
                                <a class="dash-pagination-link" href="{{ $url }}">{{ $page }}</a>
                            @endif
                        </li>
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <li>
                    <a class="dash-pagination-link" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="{{ __('pagination.next') }}">&rsaquo;</a>
                </li>
            @else
                <li>
                    <span class="dash-pagination-link is-disabled" aria-disabled="true" aria-label="{{ __('pagination.next') }}">&rsaquo;</span>
                </li>
            @endif
        </ul>
    </nav>
@endif
