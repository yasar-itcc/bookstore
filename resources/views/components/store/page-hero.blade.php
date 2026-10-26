{{-- Banner at the top of inner pages: title and breadcrumb on a soft brand gradient. --}}
@props(['title', 'crumbs' => []])

<section class="relative overflow-hidden bg-gradient-to-r from-primary-soft via-white to-amber-50">
    <div class="absolute -top-20 -right-20 size-72 rounded-full bg-cyan/10"></div>
    <div class="absolute -bottom-24 -left-16 size-64 rounded-full bg-primary/10"></div>
    <div class="store-container relative py-16 text-center md:py-20">
        <h1 class="font-serif text-4xl font-medium text-navy md:text-5xl">{{ $title }}</h1>
        <nav class="mt-4 flex items-center justify-center gap-2 text-sm text-muted" aria-label="Breadcrumb">
            <a href="{{ route('home') }}" class="hover:text-primary">Home</a>
            @foreach ($crumbs as $label => $href)
                <i class="ti ti-chevron-right text-xs"></i>
                @if ($href)<a href="{{ $href }}" class="hover:text-primary">{{ $label }}</a>@else<span class="text-navy">{{ $label }}</span>@endif
            @endforeach
        </nav>
    </div>
</section>
