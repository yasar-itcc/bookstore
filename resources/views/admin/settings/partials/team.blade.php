{{-- Repeatable list of team members: name, role, photo. --}}
<div x-data="{ items: @js(array_values($items)) }">
    <p class="admin-label">{{ $label }}</p>
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        <template x-for="(member, i) in items" :key="i">
            <div class="rounded-md border border-line p-4" x-data="{ preview: member.photo ? @js(Storage::disk('public')->url('')) + member.photo : null }">
                <div class="mb-3 flex items-center gap-3">
                    <div class="flex size-14 shrink-0 items-center justify-center overflow-hidden rounded-full bg-primary-soft text-primary">
                        <template x-if="preview"><img :src="preview" alt="" class="size-full object-cover"></template>
                        <i x-show="!preview" class="ti ti-user text-2xl"></i>
                    </div>
                    <label class="btn btn-secondary cursor-pointer px-3 py-1.5 text-xs"><i class="ti ti-upload"></i> Photo
                        <input type="file" class="sr-only" accept="image/jpeg,image/png,image/webp" :name="`{{ $input }}[${i}][photo_file]`"
                               @change="const f = $event.target.files[0]; if (f) preview = URL.createObjectURL(f)">
                    </label>
                    <button type="button" @click="items.splice(i, 1)" class="btn btn-ghost ml-auto px-2" aria-label="Remove member"><i class="ti ti-trash"></i></button>
                </div>
                <input type="hidden" :name="`{{ $input }}[${i}][photo]`" :value="member.photo">
                <input type="text" class="admin-input mb-2" :name="`{{ $input }}[${i}][name]`" x-model="member.name" placeholder="Name">
                <input type="text" class="admin-input" :name="`{{ $input }}[${i}][role]`" x-model="member.role" placeholder="Role">
            </div>
        </template>
    </div>
    <button type="button" x-show="items.length < 8" @click="items.push({ name: '', role: '', photo: null })" class="btn btn-secondary mt-3"><i class="ti ti-plus"></i> Add team member</button>
</div>
