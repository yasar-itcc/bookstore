<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Author;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AuthorController extends Controller
{
    public function index(Request $request): View
    {
        $letter = Str::upper(Str::substr((string) $request->letter, 0, 1));

        $authors = $this->visibleAuthors()
            ->when($request->search, fn ($query, $search) => $query->where('name', 'like', "%{$search}%"))
            ->when(preg_match('/^[A-Z]$/', $letter), fn ($query) => $query->where('name', 'like', "{$letter}%"))
            ->orderBy('name')
            ->paginate(24)
            ->withQueryString();

        // Letters that have at least one author, for the A–Z bar.
        $letters = $this->visibleAuthors()->pluck('name')
            ->map(fn ($name) => Str::upper(Str::substr(Str::ascii($name), 0, 1)))
            ->unique()->sort()->values();

        return view('store.authors.index', compact('authors', 'letters', 'letter'));
    }

    public function show(Author $author): View
    {
        abort_unless($author->is_active, 404);

        $books = $author->books()->where('is_active', true)->with(['authors', 'formats', 'category'])->latest()->get();

        // Other authors in the same categories, for "You may also like".
        $related = $this->visibleAuthors()
            ->whereKeyNot($author->id)
            ->whereHas('books', fn (Builder $query) => $query->whereIn('category_id', $books->pluck('category_id')))
            ->inRandomOrder()
            ->take(6)
            ->get();

        return view('store.authors.show', compact('author', 'books', 'related'));
    }

    /**
     * Active authors with at least one book on sale, with that count.
     */
    private function visibleAuthors(): Builder
    {
        $live = fn (Builder $query) => $query->where('is_active', true);

        return Author::where('is_active', true)->whereHas('books', $live)->withCount(['books' => $live]);
    }
}
