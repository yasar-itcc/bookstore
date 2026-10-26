<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Pick which books are Featured (home page spotlight) and Bestsellers (home page "Bestsellers" row).
 */
class BookHighlightController extends Controller
{
    public function index(Request $request): View
    {
        $books = Book::with(['authors', 'category', 'formats'])
            ->when($request->search, fn ($query, $search) => $query->where('title', 'like', "%{$search}%"))
            ->when($request->show === 'featured', fn ($query) => $query->where('is_featured', true))
            ->when($request->show === 'bestseller', fn ($query) => $query->where('is_bestseller', true))
            ->orderByDesc('is_featured')
            ->orderByDesc('is_bestseller')
            ->orderBy('title')
            ->paginate(20)
            ->withQueryString();

        $counts = [
            'featured' => Book::where('is_featured', true)->count(),
            'bestseller' => Book::where('is_bestseller', true)->count(),
        ];

        return view('admin.highlights.index', compact('books', 'counts'));
    }

    public function update(Request $request, Book $book): RedirectResponse
    {
        $data = $request->validate([
            'field' => ['required', Rule::in(['is_featured', 'is_bestseller'])],
            'value' => ['required', 'boolean'],
        ]);

        $book->update([$data['field'] => $data['value']]);

        $label = $data['field'] === 'is_featured' ? 'Featured' : 'Bestsellers';

        return back()->with('status', "\"{$book->title}\" ".($data['value'] ? "added to {$label}." : "removed from {$label}."));
    }
}
