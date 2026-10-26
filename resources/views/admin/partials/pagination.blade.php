@if ($paginator->hasPages() || $paginator->total() > 0)
    <div class="flex flex-wrap items-center justify-between gap-3 border-t border-line px-5 py-4 text-sm text-muted">
        <span>Showing {{ $paginator->firstItem() }} to {{ $paginator->lastItem() }} of {{ $paginator->total() }}</span>
        @if ($paginator->hasPages())
            <div class="flex items-center gap-1">
                @if ($paginator->onFirstPage())
                    <span class="flex size-8 items-center justify-center rounded-md bg-page text-muted/50"><i class="ti ti-chevron-left"></i></span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" class="flex size-8 items-center justify-center rounded-md bg-page hover:bg-primary-soft hover:text-primary" aria-label="Previous page"><i class="ti ti-chevron-left"></i></a>
                @endif
                @foreach ($paginator->getUrlRange(max(1, $paginator->currentPage() - 2), min($paginator->lastPage(), $paginator->currentPage() + 2)) as $page => $url)
                    <a href="{{ $url }}" @class([
                        'flex size-8 items-center justify-center rounded-md',
                        'bg-primary text-white shadow-sm shadow-primary/30' => $page === $paginator->currentPage(),
                        'bg-page text-ink hover:bg-primary-soft hover:text-primary' => $page !== $paginator->currentPage(),
                    ])>{{ $page }}</a>
                @endforeach
                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" class="flex size-8 items-center justify-center rounded-md bg-page hover:bg-primary-soft hover:text-primary" aria-label="Next page"><i class="ti ti-chevron-right"></i></a>
                @else
                    <span class="flex size-8 items-center justify-center rounded-md bg-page text-muted/50"><i class="ti ti-chevron-right"></i></span>
                @endif
            </div>
        @endif
    </div>
@endif
