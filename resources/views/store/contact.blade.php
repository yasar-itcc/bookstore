<x-store.layout title="Contact us" description="Questions about an order, a book or bulk purchases? Get in touch.">
    <x-store.page-hero title="Contact us" :crumbs="['Contact' => null]" />

    <section class="store-container grid gap-10 py-20 lg:grid-cols-[1fr_1.4fr]">
        <div>
            <p class="mb-3 text-sm font-semibold tracking-[0.2em] text-muted uppercase">Get in touch</p>
            <h2 class="font-serif text-4xl font-medium text-navy">We'd love to hear from you</h2>
            <p class="mt-4 leading-relaxed text-muted">Questions about an order, looking for a particular book, or buying in bulk for a school or library? Send us a message.</p>

            <div class="mt-8 space-y-4">
                @foreach (array_filter([
                    ['ti-phone', 'Phone', setting('store.phone'), 'tel:'.preg_replace('/\s+/', '', (string) setting('store.phone'))],
                    ['ti-brand-whatsapp', 'WhatsApp', setting('store.whatsapp') ? 'Chat with us' : null, 'https://wa.me/'.preg_replace('/\D+/', '', (string) setting('store.whatsapp'))],
                    ['ti-mail', 'Email', setting('store.email'), 'mailto:'.setting('store.email')],
                    ['ti-map-pin', 'Address', setting('store.address'), null],
                ], fn ($row) => filled($row[2])) as [$icon, $label, $value, $link])
                    <div class="flex items-start gap-4 rounded-xl bg-page p-5">
                        <span class="flex size-11 shrink-0 items-center justify-center rounded-lg bg-primary text-white"><i class="ti {{ $icon }} text-xl"></i></span>
                        <div>
                            <p class="text-sm text-muted">{{ $label }}</p>
                            @if ($link)
                                <a href="{{ $link }}" class="font-semibold whitespace-pre-line text-navy hover:text-primary" @if (str_starts_with($link, 'https')) target="_blank" rel="noopener" @endif>{{ $value }}</a>
                            @else
                                <p class="font-semibold whitespace-pre-line text-navy">{{ $value }}</p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="rounded-2xl border border-line bg-white p-8 shadow-soft">
            @if (session('status'))
                <div class="mb-6 flex items-center gap-3 rounded-md bg-leaf-soft px-4 py-3 text-sm text-leaf-dark" role="status"><i class="ti ti-circle-check text-xl"></i>{{ session('status') }}</div>
            @endif
            <form method="POST" action="{{ route('contact.store') }}" class="grid gap-5 sm:grid-cols-2">
                @csrf
                <div class="hidden" aria-hidden="true"><label>Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>
                @foreach ([['name', 'Your name', 'text', true], ['email', 'Email', 'email', true], ['phone', 'Phone (optional)', 'tel', false], ['subject', 'Subject', 'text', false]] as [$field, $label, $type, $required])
                    <div>
                        <label for="{{ $field }}" class="mb-1.5 block text-sm font-medium text-navy">{{ $label }}</label>
                        <input id="{{ $field }}" type="{{ $type }}" name="{{ $field }}" value="{{ old($field) }}" @required($required) class="store-input @error($field) border-amber-500 @enderror">
                        @error($field)<p class="mt-1 text-xs text-amber-700">{{ $message }}</p>@enderror
                    </div>
                @endforeach
                <div class="sm:col-span-2">
                    <label for="message" class="mb-1.5 block text-sm font-medium text-navy">Message</label>
                    <textarea id="message" name="message" rows="6" required class="store-input @error('message') border-amber-500 @enderror">{{ old('message') }}</textarea>
                    @error('message')<p class="mt-1 text-xs text-amber-700">{{ $message }}</p>@enderror
                </div>
                <div class="sm:col-span-2">
                    <button type="submit" class="btn-store">Send message <i class="ti ti-send"></i></button>
                </div>
            </form>
        </div>
    </section>
</x-store.layout>
