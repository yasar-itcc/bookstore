<?php

namespace App\Http\Controllers\Admin;

use App\Enums\BookFormat as Format;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BookRequest;
use App\Models\Author;
use App\Models\Book;
use App\Models\Category;
use App\Models\Language;
use App\Models\Publisher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class BookController extends Controller
{
    public function index(Request $request): View
    {
        $books = Book::with(['category', 'authors', 'formats'])
            ->when($request->search, function ($query, $search) {
                $query->where(fn ($q) => $q->where('title', 'like', "%{$search}%")->orWhere('isbn', 'like', "%{$search}%"));
            })
            ->when($request->category, fn ($query, $category) => $query->where('category_id', $category))
            ->when($request->status === 'live', fn ($query) => $query->where('is_active', true))
            ->when($request->status === 'hidden', fn ($query) => $query->where('is_active', false))
            ->when($request->format, fn ($query, $format) => $query->whereHas('formats', fn ($q) => $q->where('format', $format)))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $categories = Category::orderBy('name')->pluck('name', 'id');

        return view('admin.books.index', compact('books', 'categories'));
    }

    public function create(): View
    {
        return view('admin.books.form', ['book' => new Book(['is_active' => true])] + $this->formOptions());
    }

    public function store(BookRequest $request): RedirectResponse
    {
        $book = DB::transaction(function () use ($request) {
            $book = Book::create($this->bookData($request));
            $this->syncRelations($request, $book);

            return $book;
        });

        return redirect()->route('admin.books.edit', $book)->with('status', "Book \"{$book->title}\" created.");
    }

    public function edit(Book $book): View
    {
        $book->load(['authors', 'formats']);

        return view('admin.books.form', compact('book') + $this->formOptions());
    }

    public function update(BookRequest $request, Book $book): RedirectResponse
    {
        DB::transaction(function () use ($request, $book) {
            $book->update($this->bookData($request, $book));
            $this->syncRelations($request, $book);
        });

        return redirect()->route('admin.books.edit', $book)->with('status', "Book \"{$book->title}\" updated.");
    }

    public function destroy(Book $book): RedirectResponse
    {
        if ($book->cover_image) {
            Storage::disk('public')->delete($book->cover_image);
        }
        foreach ($book->formats as $format) {
            if ($format->ebook_file) {
                Storage::disk('local')->delete($format->ebook_file);
            }
        }
        $book->delete();

        return redirect()->route('admin.books.index')->with('status', "Book \"{$book->title}\" deleted.");
    }

    private function bookData(BookRequest $request, ?Book $book = null): array
    {
        $data = $request->safe()->except(['author_ids', 'formats', 'cover_image', 'remove_cover']);

        if ($request->hasFile('cover_image') || $request->boolean('remove_cover')) {
            if ($book?->cover_image) {
                Storage::disk('public')->delete($book->cover_image);
            }
            $data['cover_image'] = $request->file('cover_image')?->store('covers', 'public');
        }

        return $data;
    }

    /**
     * Save authors and the formats the book is sold in. Formats that are switched off are removed.
     */
    private function syncRelations(BookRequest $request, Book $book): void
    {
        $book->authors()->sync($request->validated('author_ids', []));

        $book->load('formats');

        foreach (Format::cases() as $format) {
            $input = $request->validated("formats.{$format->value}", []);
            $existing = $book->formats->firstWhere('format', $format);

            if (! $request->boolean("formats.{$format->value}.enabled")) {
                if ($existing) {
                    if ($existing->ebook_file) {
                        Storage::disk('local')->delete($existing->ebook_file);
                    }
                    $existing->delete();
                }

                continue;
            }

            $values = [
                'sku' => $input['sku'] ?? null,
                'mrp' => $input['mrp'],
                'price' => $input['price'],
                'gst_rate' => $input['gst_rate'],
                'is_active' => (bool) ($input['is_active'] ?? true),
                'stock' => $format->isDigital() ? null : $input['stock'],
            ];

            // eBook files are private (storage/app/private), never publicly downloadable.
            if ($format->isDigital() && $request->hasFile("formats.{$format->value}.ebook_file")) {
                if ($existing?->ebook_file) {
                    Storage::disk('local')->delete($existing->ebook_file);
                }
                $values['ebook_file'] = $request->file("formats.{$format->value}.ebook_file")->store('ebooks', 'local');
            }

            $book->formats()->updateOrCreate(['format' => $format->value], $values);
        }
    }

    private function formOptions(): array
    {
        return [
            'categories' => Category::with('parent')->orderBy('name')->get()->mapWithKeys(fn ($c) => [$c->id => $c->fullName()])->sort()->all(),
            'authors' => Author::orderBy('name')->get(['id', 'name']),
            'publishers' => Publisher::orderBy('name')->pluck('name', 'id')->all(),
            'languages' => Language::orderBy('name')->pluck('name', 'id')->all(),
        ];
    }
}
