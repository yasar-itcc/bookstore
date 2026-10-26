@props(['action', 'name'])

<form method="POST" action="{{ $action }}" class="inline"
      onsubmit="return confirm(@js("Delete \"{$name}\"? This can't be undone."))">
    @csrf
    @method('DELETE')
    <button type="submit" class="flex size-8 items-center justify-center rounded-full text-muted hover:bg-amber-50 hover:text-amber-700" title="Delete">
        <i class="ti ti-trash text-lg"></i>
    </button>
</form>
