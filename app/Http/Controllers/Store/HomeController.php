<?php

namespace App\Http\Controllers\Store;

use App\Enums\BookFormat as Format;
use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Book;
use App\Models\Category;
use App\Models\Post;
use App\Models\Testimonial;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $books = fn () => Book::query()->where('is_active', true)->with(['authors', 'formats']);

        $featured = $books()->where('is_featured', true)->latest()->take(8)->get();

        // The two category shelves: chosen in Settings → Home page, otherwise the two largest categories.
        $columnCategories = collect([setting('home.column_category_1'), setting('home.column_category_2')])
            ->filter()
            ->map(fn ($id) => Category::find($id))
            ->filter();
        if ($columnCategories->count() < 2) {
            $columnCategories = $columnCategories->merge(
                Category::has('books')->withCount('books')->whereNotIn('id', $columnCategories->pluck('id'))
                    ->orderByDesc('books_count')->take(2 - $columnCategories->count())->get()
            );
        }

        $spotlight = setting('home.spotlight_book_id')
            ? $books()->find(setting('home.spotlight_book_id'))
            : null;

        return view('home', [
            'slides' => Banner::live('hero')->get(),
            'promo' => Banner::live('promo')->first(),
            'featured' => $featured,
            'categories' => Category::whereNull('parent_id')->where('is_active', true)
                ->withCount(['books' => fn (Builder $q) => $q->where('is_active', true)])
                ->with(['children' => fn ($q) => $q->where('is_active', true)->withCount('books')])
                ->orderBy('sort_order')->get()
                ->flatMap(fn ($category) => $category->children->isNotEmpty() ? $category->children : [$category])
                ->filter(fn ($category) => $category->books_count > 0)
                ->take(8),
            'bestsellers' => $books()->where('is_bestseller', true)->latest()->take(12)->get(),
            'newArrivals' => $books()->latest()->take(10)->get(),
            'ebooks' => $books()->whereHas('formats', fn ($q) => $q->where('format', Format::Ebook->value)->where('is_active', true))->latest()->take(2)->get(),
            'columns' => $columnCategories->map(fn ($category) => [
                'category' => $category,
                'books' => $books()->where('category_id', $category->id)->latest()->take(4)->get(),
            ]),
            'spotlight' => $spotlight ?? $featured->first(),
            'testimonials' => Testimonial::where('is_active', true)->orderBy('sort_order')->take(3)->get(),
            'posts' => Post::live()->take(3)->get(),
        ]);
    }
}
