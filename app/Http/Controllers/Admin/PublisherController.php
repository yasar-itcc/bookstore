<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Publisher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PublisherController extends Controller
{
    public function index(Request $request): View
    {
        $publishers = Publisher::withCount('books')
            ->when($request->search, fn ($query, $search) => $query->where('name', 'like', "%{$search}%"))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.publishers.index', compact('publishers'));
    }

    public function create(): View
    {
        return view('admin.publishers.form', ['publisher' => new Publisher(['is_active' => true])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $publisher = Publisher::create($this->validated($request));

        return redirect()->route('admin.publishers.index')->with('status', "Publisher \"{$publisher->name}\" created.");
    }

    public function edit(Publisher $publisher): View
    {
        return view('admin.publishers.form', compact('publisher'));
    }

    public function update(Request $request, Publisher $publisher): RedirectResponse
    {
        $publisher->update($this->validated($request, $publisher));

        return redirect()->route('admin.publishers.index')->with('status', "Publisher \"{$publisher->name}\" updated.");
    }

    public function destroy(Publisher $publisher): RedirectResponse
    {
        // Books keep existing; their publisher is cleared by the database.
        $publisher->delete();

        return redirect()->route('admin.publishers.index')->with('status', "Publisher \"{$publisher->name}\" deleted.");
    }

    private function validated(Request $request, ?Publisher $publisher = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['nullable', 'alpha_dash', 'max:140', Rule::unique('publishers')->ignore($publisher)],
            'website' => ['nullable', 'url', 'max:255'],
            'is_active' => ['boolean'],
        ]);
    }
}
