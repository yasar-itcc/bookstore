<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Language;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LanguageController extends Controller
{
    public function index(Request $request): View
    {
        $languages = Language::withCount('books')
            ->when($request->search, fn ($query, $search) => $query->where('name', 'like', "%{$search}%"))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.languages.index', compact('languages'));
    }

    public function create(): View
    {
        return view('admin.languages.form', ['language' => new Language(['is_active' => true])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $language = Language::create($this->validated($request));

        return redirect()->route('admin.languages.index')->with('status', "Language \"{$language->name}\" created.");
    }

    public function edit(Language $language): View
    {
        return view('admin.languages.form', compact('language'));
    }

    public function update(Request $request, Language $language): RedirectResponse
    {
        $language->update($this->validated($request, $language));

        return redirect()->route('admin.languages.index')->with('status', "Language \"{$language->name}\" updated.");
    }

    public function destroy(Language $language): RedirectResponse
    {
        // Books keep existing; their language is cleared by the database.
        $language->delete();

        return redirect()->route('admin.languages.index')->with('status', "Language \"{$language->name}\" deleted.");
    }

    private function validated(Request $request, ?Language $language = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:60'],
            'code' => ['required', 'alpha_dash', 'max:10', Rule::unique('languages')->ignore($language)],
            'is_active' => ['boolean'],
        ]);
    }
}
