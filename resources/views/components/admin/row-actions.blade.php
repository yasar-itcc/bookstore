@props(['edit', 'destroy' => null, 'name' => ''])

<div class="flex justify-end gap-1">
    <a href="{{ $edit }}" class="flex size-8 items-center justify-center rounded-full text-muted hover:bg-page hover:text-primary" title="Edit"><i class="ti ti-pencil text-lg"></i></a>
    @if ($destroy)
        <x-admin.delete-button :action="$destroy" :name="$name" />
    @endif
</div>
