{{-- Text area with a small formatting toolbar. Content is saved as Markdown and shown as formatted text on the store. --}}
@props(['name', 'label' => 'Content', 'value' => null, 'rows' => 16])

<x-admin.field :label="$label" :name="$name" hint="Toolbar adds formatting: **bold**, *italic*, ## heading, - list, [link](https://…). Leave a blank line between paragraphs.">
    <div x-data="{
            wrap(before, after = before, placeholder = 'text') {
                const el = this.$refs.editor, s = el.selectionStart, e = el.selectionEnd;
                const picked = el.value.slice(s, e) || placeholder;
                el.setRangeText(before + picked + after, s, e, 'end');
                el.focus();
            },
            line(prefix) {
                const el = this.$refs.editor, s = el.selectionStart;
                const start = el.value.lastIndexOf('\n', s - 1) + 1;
                el.setRangeText(prefix, start, start, 'end');
                el.focus();
            },
         }" class="overflow-hidden rounded-md border border-line focus-within:border-primary focus-within:ring-3 focus-within:ring-primary/15">
        <div class="flex flex-wrap gap-1 border-b border-line bg-page/60 px-2 py-1.5">
            @foreach ([
                ['ti-bold', 'Bold', "wrap('**')"],
                ['ti-italic', 'Italic', "wrap('*')"],
                ['ti-h-2', 'Heading', "line('## ')"],
                ['ti-h-3', 'Small heading', "line('### ')"],
                ['ti-list', 'Bullet list', "line('- ')"],
                ['ti-list-numbers', 'Numbered list', "line('1. ')"],
                ['ti-quote', 'Quote', "line('> ')"],
                ['ti-link', 'Link', "wrap('[', '](https://)', 'link text')"],
            ] as [$icon, $title, $action])
                <button type="button" @click="{{ $action }}" title="{{ $title }}" aria-label="{{ $title }}"
                        class="flex size-8 items-center justify-center rounded text-ink hover:bg-white hover:text-primary">
                    <i class="ti {{ $icon }} text-lg"></i>
                </button>
            @endforeach
        </div>
        <textarea x-ref="editor" id="{{ $name }}" name="{{ $name }}" rows="{{ $rows }}"
                  class="block w-full resize-y border-0 px-3.5 py-3 font-mono text-sm leading-relaxed text-ink focus:ring-0 focus:outline-none">{{ old($name, $value) }}</textarea>
    </div>
</x-admin.field>
