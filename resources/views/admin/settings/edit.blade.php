@use('App\Models\Setting')

@php $current = $groups[$group]; @endphp

<x-admin.layout :title="$current['label'].' settings'">
    <x-admin.page-header title="Settings" :subtitle="$current['description']"
                         :breadcrumbs="['Dashboard' => route('admin.dashboard'), 'Settings' => null, $current['label'] => null]" />

    <div class="grid grid-cols-12 gap-6">
        {{-- Tabs --}}
        <nav class="col-span-12 lg:col-span-3">
            <div class="admin-card flex gap-1 overflow-x-auto p-2 lg:flex-col">
                @foreach ($groups as $key => $info)
                    <a href="{{ route('admin.settings.edit', $key) }}" @class([
                        'flex shrink-0 items-center gap-3 rounded-md px-3 py-2.5 text-sm transition',
                        'bg-primary text-white shadow-md shadow-primary/30' => $key === $group,
                        'text-ink hover:bg-page' => $key !== $group,
                    ])>
                        <i class="ti {{ $info['icon'] }} text-xl"></i> {{ $info['label'] }}
                    </a>
                @endforeach
            </div>
        </nav>

        <form method="POST" enctype="multipart/form-data" action="{{ route('admin.settings.update', $group) }}" class="col-span-12 space-y-6 lg:col-span-9">
            @csrf
            @method('PUT')

            @if ($errors->any())
                <div class="flex items-start gap-3 rounded-md bg-amber-50 px-4 py-3 text-sm text-amber-800" role="alert">
                    <i class="ti ti-alert-triangle mt-0.5 text-xl"></i> Please check the highlighted fields.
                </div>
            @endif

            @foreach ($current['sections'] as $sectionTitle => $fields)
                <div class="admin-card p-6">
                    <h2 class="mb-5 text-lg font-semibold text-navy">{{ $sectionTitle }}</h2>
                    <div class="grid gap-5 md:grid-cols-2">
                        @foreach ($fields as $key => [$label, $type, $required, $hint])
                            @php
                                $input = str_replace('.', '__', $key);
                                $value = Setting::get($key);
                                $wide = in_array($type, ['textarea', 'features', 'team', 'image']);
                            @endphp
                            <div @class(['md:col-span-2' => $wide])>
                                @switch($type)
                                    @case('toggle')
                                        <x-admin.toggle :name="$input" :label="$label" :hint="$hint" :checked="(bool) $value" />
                                        @break

                                    @case('textarea')
                                        <x-admin.textarea :name="$input" :label="$label" :value="$value" :hint="$hint" rows="4" />
                                        @break

                                    @case('image')
                                        @php $url = Setting::imageUrl($key); @endphp
                                        <x-admin.field :label="$label" :name="$input" :hint="$hint">
                                            <div class="flex flex-wrap items-center gap-4" x-data="{ preview: @js($url) }">
                                                <div class="flex h-20 w-32 items-center justify-center overflow-hidden rounded-md border border-dashed border-line bg-page">
                                                    <template x-if="preview"><img :src="preview" alt="" class="max-h-full max-w-full object-contain"></template>
                                                    <i x-show="!preview" class="ti ti-photo text-2xl text-muted"></i>
                                                </div>
                                                <label class="btn btn-secondary cursor-pointer"><i class="ti ti-upload"></i> Choose image
                                                    <input type="file" name="{{ $input }}" accept="image/jpeg,image/png,image/webp" class="sr-only"
                                                           @change="const f = $event.target.files[0]; if (f) preview = URL.createObjectURL(f)">
                                                </label>
                                                @if ($url)
                                                    <label class="flex items-center gap-1.5 text-sm text-muted"><input type="checkbox" name="{{ $input }}_remove" value="1" class="accent-primary"> Remove</label>
                                                @endif
                                            </div>
                                        </x-admin.field>
                                        @break

                                    @case('category')
                                        <x-admin.select :name="$input" :label="$label" :options="$categories" :value="$value" placeholder="Choose automatically" :hint="$hint" />
                                        @break

                                    @case('book')
                                        <x-admin.select :name="$input" :label="$label" :options="$books" :value="$value" placeholder="First featured book" :hint="$hint" />
                                        @break

                                    @case('features')
                                        @include('admin.settings.partials.features', ['input' => $input, 'label' => $label, 'hint' => $hint, 'items' => old($input, Setting::json($key))])
                                        @break

                                    @case('team')
                                        @include('admin.settings.partials.team', ['input' => $input, 'label' => $label, 'items' => old($input, Setting::json($key))])
                                        @break

                                    @default
                                        <x-admin.input :name="$input" :label="$label" :value="$value" :hint="$hint" :required="$required === 'required'"
                                                       :type="in_array($type, ['email', 'url', 'number']) ? $type : 'text'" :step="$type === 'number' ? '0.01' : null" />
                                @endswitch
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach

            <div class="sticky bottom-4 z-10 flex justify-end">
                <button type="submit" class="btn btn-primary px-6 py-2.5 shadow-lg"><i class="ti ti-device-floppy"></i> Save {{ Str::lower($current['label']) }} settings</button>
            </div>
        </form>
    </div>
</x-admin.layout>
