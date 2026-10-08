<nav class="admin-list-pagination" aria-label="{{ $label ?? 'List pagination' }}">
    <div class="admin-list-pagination__status" aria-live="polite">
        Page {{ $paginator->currentPage() }}
        @if ($paginator->firstItem() !== null)
            <span>&middot; Showing {{ $paginator->firstItem() }}&ndash;{{ $paginator->lastItem() }}</span>
        @endif
    </div>

    <div class="admin-list-pagination__actions">
        @if ($paginator->onFirstPage())
            <span class="admin-list-pagination__button is-disabled" aria-disabled="true">
                <span aria-hidden="true">&larr;</span> Previous
            </span>
        @else
            <a class="admin-list-pagination__button" href="{{ $paginator->previousPageUrl() }}" rel="prev">
                <span aria-hidden="true">&larr;</span> Previous
            </a>
        @endif

        @if ($paginator->hasMorePages())
            <a class="admin-list-pagination__button" href="{{ $paginator->nextPageUrl() }}" rel="next">
                Next <span aria-hidden="true">&rarr;</span>
            </a>
        @else
            <span class="admin-list-pagination__button is-disabled" aria-disabled="true">
                Next <span aria-hidden="true">&rarr;</span>
            </span>
        @endif
    </div>
</nav>
