{{-- Repeatable list of highlights: icon + title + short text. --}}
<div x-data="{ items: @js(array_values($items)) }">
    <p class="admin-label">{{ $label }}</p>
    @if ($hint)<p class="mb-3 text-xs text-muted">{{ $hint }}</p>@endif
    <div class="space-y-3">
        <template x-for="(item, i) in items" :key="i">
            <div class="grid items-end gap-3 rounded-md border border-line p-3 sm:grid-cols-[120px_1fr_1fr_auto]">
                <div>
                    <label class="mb-1 block text-xs text-muted">Icon</label>
                    <div class="flex items-center gap-2">
                        <i class="ti text-2xl text-primary" :class="item.icon || 'ti-star'"></i>
                        <input type="text" class="admin-input" :name="`{{ $input }}[${i}][icon]`" x-model="item.icon" placeholder="ti-truck">
                    </div>
                </div>
                <div><label class="mb-1 block text-xs text-muted">Title</label><input type="text" class="admin-input" :name="`{{ $input }}[${i}][title]`" x-model="item.title"></div>
                <div><label class="mb-1 block text-xs text-muted">Text</label><input type="text" class="admin-input" :name="`{{ $input }}[${i}][text]`" x-model="item.text"></div>
                <button type="button" @click="items.splice(i, 1)" class="btn btn-ghost" aria-label="Remove"><i class="ti ti-trash"></i></button>
            </div>
        </template>
    </div>
    <button type="button" x-show="items.length < 8" @click="items.push({ icon: 'ti-star', title: '', text: '' })" class="btn btn-secondary mt-3"><i class="ti ti-plus"></i> Add highlight</button>
    <p class="mt-2 text-xs text-muted">Icon names come from <a href="https://tabler.io/icons" target="_blank" rel="noopener" class="text-primary hover:underline">tabler.io/icons</a>, written with “ti-” in front, e.g. ti-truck, ti-gift, ti-book.</p>
</div>
