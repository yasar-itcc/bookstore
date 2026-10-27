@use('App\Models\Setting')

{{-- Used for Settings (store, email, SEO) and for Content → Pages (home, about, pricing text). --}}
@php
    $current = $groups[$group];
    $isPages = $scope === 'pages';
@endphp

<x-admin.layout :title="$isPages ? $current['label'] : $current['label'].' settings'">
    <x-admin.page-header :title="$isPages ? $current['label'] : 'Settings'" :subtitle="$current['description']"
                         :breadcrumbs="$isPages
                            ? ['Dashboard' => route('admin.dashboard'), 'Pages' => route('admin.pages.index'), $current['label'] => null]
                            : ['Dashboard' => route('admin.dashboard'), 'Settings' => null, $current['label'] => null]">
        @if ($isPages)
            <x-slot:actions>
                @if ($group === 'home')
                    <a href="{{ route('admin.banners.index') }}" class="btn btn-secondary"><i class="ti ti-photo"></i> Edit slider</a>
                @endif
                @if ($group === 'pricing')
                    <a href="{{ route('admin.pricing-plans.index') }}" class="btn btn-secondary"><i class="ti ti-crown"></i> Edit plans</a>
                @endif
                <a href="{{ url($current['url']) }}" target="_blank" class="btn btn-secondary"><i class="ti ti-external-link"></i> View page</a>
            </x-slot:actions>
        @endif
    </x-admin.page-header>

    <div class="grid grid-cols-12 gap-6">
        {{-- Tabs --}}
        <nav class="col-span-12 lg:col-span-3">
            <div class="admin-card flex gap-1 overflow-x-auto p-2 lg:flex-col">
                @foreach ($groups as $key => $info)
                    <a href="{{ route("{$route}.edit", $key) }}" @class([
                        'flex shrink-0 items-center gap-3 rounded-md px-3 py-2.5 text-sm transition',
                        'bg-primary text-white shadow-md shadow-primary/30' => $key === $group,
                        'text-ink hover:bg-page' => $key !== $group,
                    ])>
                        <i class="ti {{ $info['icon'] }} text-xl"></i> {{ $info['label'] }}
                    </a>
                @endforeach
                @if ($isPages)
                    <a href="{{ route('admin.pages.index') }}" class="flex shrink-0 items-center gap-3 rounded-md px-3 py-2.5 text-sm text-muted hover:bg-page hover:text-ink lg:mt-2 lg:border-t lg:border-line lg:pt-3">
                        <i class="ti ti-arrow-left text-xl"></i> All pages
                    </a>
                @endif
            </div>
        </nav>

        <form method="POST" enctype="multipart/form-data" action="{{ route("{$route}.update", $group) }}" class="col-span-12 space-y-6 lg:col-span-9">
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
                                $wide = in_array($type, ['textarea', 'features', 'team', 'image', 'theme']);
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

                                    @case('theme')
                                        <x-admin.field :label="$label" :name="$input" :hint="$hint">
                                            <div class="grid gap-3 sm:grid-cols-2">
                                                @foreach (\App\Support\Themes::all() as $themeKey => $theme)
                                                    <label class="flex cursor-pointer flex-col gap-3 rounded-lg border-2 border-line p-4 transition has-checked:border-primary has-checked:bg-primary-soft/40">
                                                        <span class="flex items-center gap-2">
                                                            <input type="radio" name="{{ $input }}" value="{{ $themeKey }}" class="accent-primary" @checked(old($input, $value ?: \App\Support\Themes::DEFAULT) === $themeKey)>
                                                            <span class="font-semibold text-navy">{{ $theme['label'] }}</span>
                                                            <a href="{{ url('/?theme='.$themeKey) }}" target="_blank" class="ml-auto text-xs text-primary hover:underline" @click.stop>Preview <i class="ti ti-external-link"></i></a>
                                                        </span>
                                                        <span class="flex overflow-hidden rounded-md">
                                                            @foreach (['navy', 'primary', 'cyan', 'leaf', 'primary-soft', 'page'] as $swatch)
                                                                <span class="h-8 flex-1" style="background: {{ $theme['colors'][$swatch] }}"></span>
                                                            @endforeach
                                                        </span>
                                                        <span class="text-xs text-muted">{{ $theme['description'] }}</span>
                                                    </label>
                                                @endforeach
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
                <button type="submit" class="btn btn-primary px-6 py-2.5 shadow-lg"><i class="ti ti-device-floppy"></i> Save {{ Str::lower($current['label']) }}{{ $isPages ? '' : ' settings' }}</button>
            </div>
        </form>
    </div>
</x-admin.layout>
