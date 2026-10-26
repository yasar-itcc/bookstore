@use('App\Enums\BookFormat', 'Format')

@php
    $editing = $book->exists;
    $selectedAuthors = collect(old('author_ids', $book->authors?->pluck('id')->all() ?? []))->map(fn ($id) => (int) $id)->all();
@endphp

<x-admin.layout :title="$editing ? 'Edit book' : 'Add book'">
    <x-admin.page-header :title="$editing ? 'Edit book' : 'Add a new book'"
                         :subtitle="$editing ? 'Last updated '.$book->updated_at->diffForHumans() : 'Fill in the details, then choose the formats you sell it in.'"
                         :breadcrumbs="['Dashboard' => route('admin.dashboard'), 'Books' => route('admin.books.index'), ($editing ? Str::limit($book->title, 40) : 'Add') => null]">
        <x-slot:actions>
            <a href="{{ route('admin.books.index') }}" class="btn btn-secondary">Cancel</a>
            <button type="submit" form="book-form" class="btn btn-primary"><i class="ti ti-device-floppy"></i> {{ $editing ? 'Save changes' : 'Publish book' }}</button>
        </x-slot:actions>
    </x-admin.page-header>

    @if ($errors->any())
        <div class="mb-5 flex items-start gap-3 rounded-md bg-amber-50 px-4 py-3 text-sm text-amber-800" role="alert">
            <i class="ti ti-alert-triangle mt-0.5 text-xl"></i>
            <div>
                <p class="font-medium">Please check the highlighted fields.</p>
                @error('formats')<p class="mt-1">{{ $message }}</p>@enderror
            </div>
        </div>
    @endif

    <form id="book-form" method="POST" enctype="multipart/form-data"
          action="{{ $editing ? route('admin.books.update', $book) : route('admin.books.store') }}"
          class="grid grid-cols-12 gap-6">
        @csrf
        @if ($editing) @method('PUT') @endif

        {{-- Left column --}}
        <div class="col-span-12 space-y-6 lg:col-span-8">
            <div class="admin-card space-y-5 p-6">
                <h2 class="text-lg font-semibold text-navy">Book information</h2>
                <x-admin.input name="title" label="Title" :value="$book->title" required maxlength="200" placeholder="Wings of Fire" />
                <x-admin.input name="subtitle" label="Subtitle" :value="$book->subtitle" maxlength="200" placeholder="An Autobiography" />
                <div class="grid gap-5 sm:grid-cols-2">
                    <x-admin.input name="isbn" label="ISBN" :value="$book->isbn" maxlength="20" placeholder="9788173711466" />
                    <x-admin.input name="slug" label="URL slug" :value="$book->slug" hint="Leave empty to create it from the title." />
                </div>
                <x-admin.textarea name="description" label="Description" :value="$book->description" rows="7"
                                  placeholder="What is the book about? This shows on the book page." />
            </div>

            {{-- Formats and pricing --}}
            <div class="admin-card p-6">
                <h2 class="text-lg font-semibold text-navy">Formats and pricing</h2>
                <p class="mb-5 text-sm text-muted">Turn on each format you sell. Prices are in rupees (₹).</p>

                <div class="space-y-4">
                    @foreach (Format::cases() as $format)
                        @php
                            $key = "formats.{$format->value}";
                            $field = "formats[{$format->value}]";
                            $current = $book->formats?->firstWhere('format', $format);
                            $enabled = (bool) old("{$key}.enabled", $current !== null);
                        @endphp
                        <div x-data="{ on: @js($enabled) }" class="rounded-lg border transition"
                             :class="on ? 'border-primary/40 bg-primary-soft/30' : 'border-line'">
                            <label class="flex cursor-pointer items-center gap-3 p-4">
                                <input type="hidden" name="{{ $field }}[enabled]" value="0">
                                <input type="checkbox" name="{{ $field }}[enabled]" value="1" x-model="on" class="peer sr-only">
                                <span class="relative h-5 w-9 shrink-0 rounded-full bg-slate-300 transition peer-checked:bg-primary peer-focus-visible:ring-3 peer-focus-visible:ring-primary/25 after:absolute after:top-0.5 after:left-0.5 after:size-4 after:rounded-full after:bg-white after:shadow after:transition peer-checked:after:translate-x-4"></span>
                                <span class="flex size-9 items-center justify-center rounded-md bg-white text-primary shadow-sm"><i class="ti {{ $format->icon() }} text-xl"></i></span>
                                <span class="flex-1">
                                    <span class="block font-medium text-navy">{{ $format->label() }}</span>
                                    <span class="block text-xs text-muted">{{ $format->isDigital() ? 'Downloadable PDF or EPUB · needs online payment (not COD)' : 'Printed copy · Cash on Delivery' }}</span>
                                </span>
                            </label>

                            <div x-show="on" x-cloak class="grid gap-4 border-t border-line/70 p-4 sm:grid-cols-2 xl:grid-cols-4">
                                <x-admin.input :name="$field.'[mrp]'" label="MRP (₹)" type="number" step="0.01" min="0" :value="$current?->mrp" placeholder="299" />
                                <x-admin.input :name="$field.'[price]'" label="Selling price (₹)" type="number" step="0.01" min="0" :value="$current?->price" placeholder="249" />
                                @if ($format->isDigital())
                                    <x-admin.input :name="$field.'[gst_rate]'" label="GST %" type="number" step="0.01" min="0" max="28"
                                                   :value="$current?->gst_rate ?? $format->defaultGstRate()" />
                                    <x-admin.input :name="$field.'[sku]'" label="SKU" :value="$current?->sku" placeholder="Optional" />
                                    <div class="sm:col-span-2 xl:col-span-4">
                                        <x-admin.field label="eBook file" :name="$field.'[ebook_file]'" hint="PDF or EPUB, up to 50 MB. Stored privately: customers only get it after paying.">
                                            <input type="file" name="{{ $field }}[ebook_file]" accept=".pdf,.epub,application/pdf,application/epub+zip"
                                                   class="block w-full text-sm text-muted file:mr-3 file:rounded-md file:border-0 file:bg-primary-soft file:px-4 file:py-2 file:text-sm file:font-medium file:text-primary hover:file:bg-primary/15">
                                        </x-admin.field>
                                        @if ($current?->ebook_file)
                                            <p class="mt-2 flex items-center gap-1.5 text-xs text-leaf-dark"><i class="ti ti-file-check"></i> File uploaded: {{ basename($current->ebook_file) }}. Choose a new file to replace it.</p>
                                        @elseif ($current)
                                            <p class="mt-2 flex items-center gap-1.5 text-xs text-amber-700"><i class="ti ti-alert-circle"></i> No file uploaded yet.</p>
                                        @endif
                                    </div>
                                @else
                                    <x-admin.input :name="$field.'[stock]'" label="Stock (copies)" type="number" min="0" :value="$current?->stock ?? 0" />
                                    <x-admin.input :name="$field.'[gst_rate]'" label="GST %" type="number" step="0.01" min="0" max="28"
                                                   :value="$current?->gst_rate ?? $format->defaultGstRate()" />
                                    <x-admin.input :name="$field.'[sku]'" label="SKU" :value="$current?->sku" placeholder="Optional" />
                                @endif
                                <div class="sm:col-span-2 xl:col-span-4">
                                    <x-admin.toggle :name="$field.'[is_active]'" label="Available to buy" :checked="$current?->is_active ?? true"
                                                    hint="Turn off to hide this format without deleting its details." />
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <p class="mt-4 text-xs text-muted">GST defaults: printed books 0%, eBooks 18%. Confirm the rates with your CA.</p>
            </div>
        </div>

        {{-- Right column --}}
        <div class="col-span-12 space-y-6 lg:col-span-4">
            <div class="admin-card p-6">
                @include('admin.partials.image-upload', [
                    'name' => 'cover_image', 'label' => 'Cover image', 'removeName' => 'remove_cover',
                    'current' => $book->coverUrl(), 'hint' => 'Portrait images look best (3:4).',
                ])
            </div>

            <div class="admin-card space-y-5 p-6">
                <h2 class="text-lg font-semibold text-navy">Organize</h2>

                @if (empty($categories))
                    <p class="rounded-md bg-amber-50 px-3 py-2 text-sm text-amber-800">
                        Create a category first. <a href="{{ route('admin.categories.create') }}" class="font-medium underline">Add category</a>
                    </p>
                @endif
                <x-admin.select name="category_id" label="Category" :options="$categories" :value="$book->category_id" placeholder="Select a category" required />

                {{-- Authors: searchable checklist --}}
                <div x-data="{
                        all: @js($authors->map(fn ($a) => ['id' => $a->id, 'name' => $a->name])),
                        selected: @js($selectedAuthors),
                        q: '',
                        get filtered() { return this.all.filter(a => a.name.toLowerCase().includes(this.q.toLowerCase())) },
                        name(id) { return (this.all.find(a => a.id === id) || {}).name },
                        toggle(id) { this.selected.includes(id) ? this.selected = this.selected.filter(s => s !== id) : this.selected.push(id) },
                     }">
                    <p class="admin-label">Authors</p>
                    <template x-for="id in selected" :key="id">
                        <input type="hidden" name="author_ids[]" :value="id">
                    </template>
                    <div class="mb-2 flex flex-wrap gap-1.5" x-show="selected.length">
                        <template x-for="id in selected" :key="id">
                            <span class="inline-flex items-center gap-1 rounded bg-primary-soft px-2 py-0.5 text-xs font-medium text-primary">
                                <span x-text="name(id)"></span>
                                <button type="button" @click="toggle(id)" aria-label="Remove author"><i class="ti ti-x"></i></button>
                            </span>
                        </template>
                    </div>
                    @if ($authors->isEmpty())
                        <p class="text-sm text-muted">No authors yet. <a href="{{ route('admin.authors.create') }}" class="text-primary hover:underline">Add an author</a></p>
                    @else
                        <input type="search" x-model="q" placeholder="Find an author" class="admin-input mb-2">
                        <div class="max-h-40 overflow-y-auto rounded-md border border-line">
                            <template x-for="author in filtered" :key="author.id">
                                <label class="flex cursor-pointer items-center gap-2 px-3 py-1.5 text-sm hover:bg-page">
                                    <input type="checkbox" class="accent-primary" :checked="selected.includes(author.id)" @change="toggle(author.id)">
                                    <span x-text="author.name"></span>
                                </label>
                            </template>
                            <p x-show="!filtered.length" class="px-3 py-2 text-sm text-muted">No match.</p>
                        </div>
                    @endif
                    @error('author_ids.*')<p class="mt-1.5 text-xs text-amber-700">{{ $message }}</p>@enderror
                </div>

                <x-admin.select name="publisher_id" label="Publisher" :options="$publishers" :value="$book->publisher_id" placeholder="None" />
                <x-admin.select name="language_id" label="Language" :options="$languages" :value="$book->language_id" placeholder="Select a language" />
                <div class="grid grid-cols-2 gap-4">
                    <x-admin.input name="pages" label="Pages" type="number" min="1" :value="$book->pages" />
                    <x-admin.input name="published_year" label="Year" type="number" min="1000" :max="now()->year + 1" :value="$book->published_year" />
                </div>
            </div>

            <div class="admin-card space-y-5 p-6">
                <h2 class="text-lg font-semibold text-navy">Visibility</h2>
                <x-admin.toggle name="is_active" label="Live in store" hint="Customers can see and buy this book." :checked="$book->is_active" />
                <x-admin.toggle name="is_featured" label="Featured" hint="Home page slider and spotlight." :checked="$book->is_featured" />
                <x-admin.toggle name="is_bestseller" label="Bestseller" hint="Show in “Bestsellers of the week”." :checked="$book->is_bestseller" />
            </div>

            <button type="submit" class="btn btn-primary w-full py-2.5"><i class="ti ti-device-floppy"></i> {{ $editing ? 'Save changes' : 'Publish book' }}</button>
        </div>
    </form>
</x-admin.layout>
