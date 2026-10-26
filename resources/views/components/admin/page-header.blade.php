@props(['title', 'subtitle' => null, 'breadcrumbs' => []])

<div class="mb-6 flex flex-wrap items-end justify-between gap-4">
    <div>
        @if ($breadcrumbs)
            <nav class="mb-1 flex items-center gap-1.5 text-sm text-muted" aria-label="Breadcrumb">
                @foreach ($breadcrumbs as $label => $url)
                    @if ($url)
                        <a href="{{ $url }}" class="hover:text-primary">{{ $label }}</a>
                        <i class="ti ti-chevron-right text-xs"></i>
                    @else
                        <span class="text-ink">{{ $label }}</span>
                    @endif
                @endforeach
            </nav>
        @endif
        <h1 class="text-2xl font-semibold text-navy">{{ $title }}</h1>
        @if ($subtitle)
            <p class="mt-1 text-sm text-muted">{{ $subtitle }}</p>
        @endif
    </div>
    @isset($actions)
        <div class="flex flex-wrap items-center gap-2">{{ $actions }}</div>
    @endisset
</div>
