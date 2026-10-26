@php $editing = $post->exists; @endphp

<x-admin.layout :title="$editing ? 'Edit post' : 'Write post'">
    <x-admin.page-header :title="$editing ? 'Edit post' : 'Write a post'"
                         :breadcrumbs="['Dashboard' => route('admin.dashboard'), 'Blog posts' => route('admin.posts.index'), ($editing ? Str::limit($post->title, 40) : 'New') => null]">
        <x-slot:actions>
            @if ($editing && $post->is_published)
                <a href="{{ route('blog.show', $post->slug) }}" target="_blank" class="btn btn-secondary"><i class="ti ti-external-link"></i> View post</a>
            @endif
            <button type="submit" form="post-form" class="btn btn-primary"><i class="ti ti-device-floppy"></i> {{ $editing ? 'Save changes' : 'Publish' }}</button>
        </x-slot:actions>
    </x-admin.page-header>

    <form id="post-form" method="POST" enctype="multipart/form-data" action="{{ $editing ? route('admin.posts.update', $post) : route('admin.posts.store') }}" class="grid grid-cols-12 gap-6">
        @csrf
        @if ($editing) @method('PUT') @endif

        <div class="admin-card col-span-12 space-y-5 p-6 lg:col-span-8">
            <x-admin.input name="title" label="Title" :value="$post->title" required maxlength="200" placeholder="10 books every Indian student should read" />
            <x-admin.textarea name="excerpt" label="Summary" :value="$post->excerpt" rows="2" hint="One or two sentences shown on the blog list. Leave empty to use the start of the post." />
            <x-admin.markdown name="content" :value="$post->content" rows="22" />
        </div>

        <div class="col-span-12 space-y-6 lg:col-span-4">
            <div class="admin-card p-6">
                @include('admin.partials.image-upload', [
                    'name' => 'cover_image', 'label' => 'Cover image', 'removeName' => 'remove_cover', 'maxMb' => 4,
                    'aspect' => 'aspect-[16/10]', 'current' => $post->coverUrl(), 'hint' => 'Wide image, about 1200×750.',
                ])
            </div>
            <div class="admin-card space-y-5 p-6">
                <x-admin.input name="category" label="Category" :value="$post->category" list="post-categories" placeholder="Reading lists" />
                <datalist id="post-categories">@foreach ($categories as $category)<option value="{{ $category }}">@endforeach</datalist>
                <x-admin.input name="published_at" type="datetime-local" label="Publish date" :value="$post->published_at?->format('Y-m-d\TH:i')" hint="A future date schedules the post." />
                <x-admin.toggle name="is_published" label="Published" hint="Drafts aren't visible on the store." :checked="$post->is_published" />
                <x-admin.input name="slug" label="Web address" :value="$post->slug" hint="Leave empty to create it from the title." />
                <x-admin.textarea name="meta_description" label="Search description" :value="$post->meta_description" rows="3" />
            </div>
        </div>
    </form>
</x-admin.layout>
