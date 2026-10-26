<x-admin.layout title="Blog posts">
    <x-admin.page-header title="Blog posts" subtitle="Articles shown on your blog and on the home page."
                         :breadcrumbs="['Dashboard' => route('admin.dashboard'), 'Content' => null]">
        <x-slot:actions>
            <a href="{{ route('blog.index') }}" target="_blank" class="btn btn-secondary"><i class="ti ti-external-link"></i> View blog</a>
            <a href="{{ route('admin.posts.create') }}" class="btn btn-primary"><i class="ti ti-plus"></i> Write post</a>
        </x-slot:actions>
    </x-admin.page-header>

    <div class="admin-card overflow-hidden">
        @include('admin.partials.search', ['placeholder' => 'Search posts'])

        @if ($posts->isEmpty())
            <x-admin.empty-state icon="ti-article" title="No posts yet" text="Reading lists, author spotlights and book news help customers find you on Google.">
                <a href="{{ route('admin.posts.create') }}" class="btn btn-primary"><i class="ti ti-plus"></i> Write post</a>
            </x-admin.empty-state>
        @else
            <div class="overflow-x-auto">
                <table class="admin-table">
                    <thead><tr><th>Post</th><th>Category</th><th>Author</th><th>Date</th><th>Status</th><th class="text-right">Actions</th></tr></thead>
                    <tbody>
                    @foreach ($posts as $post)
                        @php $scheduled = $post->is_published && $post->published_at?->isFuture(); @endphp
                        <tr>
                            <td class="min-w-72">
                                <a href="{{ route('admin.posts.edit', $post) }}" class="flex items-center gap-3">
                                    @if ($post->coverUrl())
                                        <img src="{{ $post->coverUrl() }}" alt="" class="h-11 w-16 shrink-0 rounded object-cover">
                                    @else
                                        <span class="flex h-11 w-16 shrink-0 items-center justify-center rounded bg-primary-soft text-primary"><i class="ti ti-article"></i></span>
                                    @endif
                                    <span class="font-medium text-navy hover:text-primary">{{ $post->title }}</span>
                                </a>
                            </td>
                            <td class="text-muted">{{ $post->category ?? '—' }}</td>
                            <td class="text-muted">{{ $post->author?->name ?? '—' }}</td>
                            <td class="text-muted">{{ $post->published_at?->format('d M Y') }}</td>
                            <td>
                                @if ($scheduled)
                                    <span class="rounded bg-cyan-soft px-2 py-0.5 text-xs font-medium text-cyan">Scheduled</span>
                                @else
                                    <x-admin.status-badge :active="$post->is_published" on="Published" off="Draft" />
                                @endif
                            </td>
                            <td><x-admin.row-actions :edit="route('admin.posts.edit', $post)" :destroy="route('admin.posts.destroy', $post)" :name="$post->title" /></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            @include('admin.partials.pagination', ['paginator' => $posts])
        @endif
    </div>
</x-admin.layout>
