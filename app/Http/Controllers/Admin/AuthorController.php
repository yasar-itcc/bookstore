<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Author;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AuthorController extends Controller
{
    public function index(Request $request): View
    {
        $authors = Author::withCount('books')
            ->when($request->search, fn ($query, $search) => $query->where('name', 'like', "%{$search}%"))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.authors.index', compact('authors'));
    }

    public function create(): View
    {
        return view('admin.authors.form', ['author' => new Author(['is_active' => true])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $author = Author::create($this->validated($request));

        return redirect()->route('admin.authors.index')->with('status', "Author \"{$author->name}\" created.");
    }

    public function edit(Author $author): View
    {
        return view('admin.authors.form', compact('author'));
    }

    public function update(Request $request, Author $author): RedirectResponse
    {
        $author->update($this->validated($request, $author));

        return redirect()->route('admin.authors.index')->with('status', "Author \"{$author->name}\" updated.");
    }

    public function destroy(Author $author): RedirectResponse
    {
        if ($author->photo) {
            Storage::disk('public')->delete($author->photo);
        }
        $author->delete();

        return redirect()->route('admin.authors.index')->with('status', "Author \"{$author->name}\" deleted.");
    }

    private function validated(Request $request, ?Author $author = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['nullable', 'alpha_dash', 'max:140', Rule::unique('authors')->ignore($author)],
            'bio' => ['nullable', 'string', 'max:5000'],
            'is_active' => ['boolean'],
            'photo' => ['nullable', 'image', 'max:2048'],
            'remove_photo' => ['boolean'],
        ]);

        if ($request->hasFile('photo') || $request->boolean('remove_photo')) {
            if ($author?->photo) {
                Storage::disk('public')->delete($author->photo);
            }
            $data['photo'] = $request->file('photo')?->store('authors', 'public');
        }
        unset($data['remove_photo']);

        return $data;
    }
}
