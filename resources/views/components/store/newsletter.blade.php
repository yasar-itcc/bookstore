{{-- Newsletter signup band. --}}
<section id="newsletter" class="store-container py-10">
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-navy to-primary px-6 py-12 text-center text-white md:px-16">
        <div class="absolute -top-16 -left-10 size-56 rounded-full bg-cyan/20"></div>
        <div class="absolute -right-10 -bottom-20 size-64 rounded-full bg-leaf/20"></div>
        <div class="relative mx-auto max-w-2xl">
            <i class="ti ti-mail-heart mb-3 inline-block text-5xl text-cyan"></i>
            <h2 class="font-serif text-3xl font-medium md:text-4xl">{{ setting('home.newsletter_title') }}</h2>
            <p class="mt-3 text-white/80">{{ setting('home.newsletter_text') }}</p>

            @if (session('newsletter'))
                <p class="mx-auto mt-6 flex max-w-md items-center justify-center gap-2 rounded-md bg-white/15 px-4 py-3 text-sm"><i class="ti ti-circle-check text-xl text-leaf"></i>{{ session('newsletter') }}</p>
            @else
                <form method="POST" action="{{ route('newsletter.store') }}" class="mx-auto mt-7 flex max-w-lg flex-col gap-3 sm:flex-row">
                    @csrf
                    <label for="newsletter-email" class="sr-only">Email address</label>
                    <input id="newsletter-email" type="email" name="email" required value="{{ old('email') }}" placeholder="you@example.com" class="store-input flex-1 border-0">
                    <button type="submit" class="inline-flex items-center justify-center rounded-md bg-leaf px-6 py-3 text-sm font-semibold tracking-wide text-white uppercase hover:bg-leaf-dark">Subscribe</button>
                </form>
                @error('email', 'newsletter')<p class="mt-2 text-sm text-amber-200">{{ $message }}</p>@enderror
            @endif
        </div>
    </div>
</section>
