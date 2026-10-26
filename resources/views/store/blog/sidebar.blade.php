<aside class="space-y-8">
    <form action="{{ route('blog.index') }}" method="GET" class="rounded-xl bg-page p-6">
        <p class="mb-3 font-serif text-xl text-navy">Search</p>
        <div class="flex gap-2">
            <input type="search" name="search" value="{{ request('search') }}" placeholder="Search posts" class="store-input">
            <button type="submit" class="flex w-12 shrink-0 items-center justify-center rounded-md bg-primary text-white hover:bg-primary-dark" aria-label="Search"><i class="ti ti-search"></i></button>
        </div>
    </form>

    @if ($blogCategories->isNotEmpty())
        <div class="rounded-xl bg-page p-6">
            <p class="mb-3 font-serif text-xl text-navy">Categories</p>
            <ul class="divide-y divide-line">
                @foreach ($blogCategories as $name => $total)
                    <li>
                        <a href="{{ route('blog.index', ['category' => $name]) }}" @class(['flex justify-between py-2.5 text-sm hover:text-primary', 'font-semibold text-primary' => request('category') === $name, 'text-ink' => request('category') !== $name])>
                            <span>{{ $name }}</span><span class="text-muted">{{ $total }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="rounded-xl bg-page p-6">
        <p class="mb-4 font-serif text-xl text-navy">Recent posts</p>
        <div class="space-y-4">
            @foreach ($recent as $item)
                <a href="{{ route('blog.show', $item->slug) }}" class="group flex gap-3">
                    @if ($item->coverUrl())<img src="{{ $item->coverUrl() }}" alt="" class="h-16 w-20 shrink-0 rounded object-cover">@endif
                    <span>
                        <span class="line-clamp-2 text-sm font-medium text-navy group-hover:text-primary">{{ $item->title }}</span>
                        <span class="text-xs text-muted">{{ $item->published_at->format('d M Y') }}</span>
                    </span>
                </a>
            @endforeach
        </div>
    </div>

    <div class="rounded-xl bg-gradient-to-br from-navy to-primary p-6 text-white">
        <p class="font-serif text-xl">Find your next read</p>
        <p class="mt-2 text-sm text-white/80">Browse our full catalog with Cash on Delivery across India.</p>
        <a href="{{ route('books.index') }}" class="mt-4 inline-flex rounded-md bg-white px-4 py-2 text-sm font-semibold text-primary hover:bg-primary-soft">Shop books</a>
    </div>
</aside>
