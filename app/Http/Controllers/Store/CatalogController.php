<?php

namespace App\Http\Controllers\Store;

use App\Enums\BookFormat as Format;
use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\BookFormat;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Browse and view books. Cart and checkout come in phase 3.
 */
class CatalogController extends Controller
{
    public function index(Request $request): View
    {
        $category = $request->category ? Category::with('children')->where('slug', $request->category)->first() : null;

        $books = Book::query()
            ->where('is_active', true)
            ->with(['authors', 'formats', 'category'])
            ->when($request->search, function ($query, $search) {
                $query->where(fn ($q) => $q->where('title', 'like', "%{$search}%")
                    ->orWhere('isbn', 'like', "%{$search}%")
                    ->orWhereHas('authors', fn ($a) => $a->where('name', 'like', "%{$search}%")));
            })
            ->when($category, fn ($query) => $query->whereIn('category_id', $category->children->pluck('id')->push($category->id)))
            ->when(in_array($request->format, array_column(Format::cases(), 'value'), true),
                fn ($query) => $query->whereHas('formats', fn ($q) => $q->where('format', $request->format)->where('is_active', true)))
            ->when($request->show === 'bestsellers', fn ($query) => $query->where('is_bestseller', true))
            ->when($request->show === 'featured', fn ($query) => $query->where('is_featured', true));

        match ($request->sort) {
            'price_low' => $books->orderBy($this->minPrice()),
            'price_high' => $books->orderByDesc($this->minPrice()),
            'title' => $books->orderBy('title'),
            default => $books->latest(),
        };

        return view('store.books.index', [
            'books' => $books->paginate(20)->withQueryString(),
            'category' => $category,
            'categories' => Category::whereNull('parent_id')->where('is_active', true)->with(['children' => fn ($q) => $q->where('is_active', true)])->orderBy('sort_order')->get(),
        ]);
    }

    public function show(Book $book): View
    {
        abort_unless($book->is_active, 404);

        $book->load(['authors', 'formats', 'category.parent', 'publisher', 'language']);

        return view('store.books.show', [
            'book' => $book,
            'related' => Book::where('is_active', true)->where('category_id', $book->category_id)->whereKeyNot($book->id)
                ->with(['authors', 'formats'])->inRandomOrder()->take(5)->get(),
        ]);
    }

    private function minPrice()
    {
        return BookFormat::selectRaw('MIN(price)')->whereColumn('book_id', 'books.id')->where('is_active', true);
    }
}
