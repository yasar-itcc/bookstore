{{-- Image picker with live preview. Vars: $name, $label, $current (URL or null), $removeName, $hint and $aspect (optional, default portrait 3:4). --}}
<div x-data="{ preview: @js($current), remove: false }">
    <p class="admin-label">{{ $label }}</p>
    <div class="relative flex {{ $aspect ?? 'aspect-[3/4] max-w-56' }} w-full items-center justify-center overflow-hidden rounded-md border-2 border-dashed border-line bg-page">
        <template x-if="preview && !remove">
            <img :src="preview" alt="" class="absolute inset-0 size-full object-cover">
        </template>
        <div x-show="!preview || remove" class="p-4 text-center text-sm text-muted">
            <i class="ti ti-photo-up mb-2 block text-3xl text-primary"></i>
            JPG, PNG or WebP, up to {{ $maxMb ?? 2 }} MB
        </div>
    </div>
    <div class="mt-3 flex flex-wrap items-center gap-2">
        <label class="btn btn-secondary cursor-pointer">
            <i class="ti ti-upload"></i> Choose image
            <input type="file" name="{{ $name }}" accept="image/jpeg,image/png,image/webp" class="sr-only"
                   @change="const f = $event.target.files[0]; if (f) { preview = URL.createObjectURL(f); remove = false }">
        </label>
        @if ($current)
            <label class="flex items-center gap-1.5 text-sm text-muted">
                <input type="checkbox" name="{{ $removeName }}" value="1" x-model="remove" class="accent-primary"> Remove
            </label>
        @endif
    </div>
    @error($name)
        <p class="mt-1.5 flex items-center gap-1 text-xs text-amber-700"><i class="ti ti-alert-circle"></i>{{ $message }}</p>
    @enderror
    @isset($hint)
        <p class="mt-1.5 text-xs text-muted">{{ $hint }}</p>
    @endisset
</div>
