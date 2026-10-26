{{-- Search box at the top of a list card. --}}
<form method="GET" class="flex flex-wrap items-center gap-3 border-b border-line p-5">
    <div class="relative min-w-56 flex-1 sm:max-w-xs">
        <i class="ti ti-search pointer-events-none absolute top-1/2 left-3 -translate-y-1/2 text-muted"></i>
        <input type="search" name="search" value="{{ request('search') }}" placeholder="{{ $placeholder ?? 'Search' }}" class="admin-input pl-9">
    </div>
    <button type="submit" class="btn btn-secondary">Search</button>
    @if (request()->hasAny(['search', 'category', 'status', 'format']))
        <a href="{{ url()->current() }}" class="text-sm text-muted hover:text-primary">Clear</a>
    @endif
</form>
