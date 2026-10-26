<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PageController extends Controller
{
    public function index(): View
    {
        return view('admin.pages.index', ['pages' => Page::orderBy('title')->get()]);
    }

    public function create(): View
    {
        return view('admin.pages.form', ['page' => new Page(['is_published' => true, 'show_in_footer' => true])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $page = Page::create($this->validated($request));

        return redirect()->route('admin.pages.edit', $page)->with('status', "Page \"{$page->title}\" created.");
    }

    public function edit(Page $page): View
    {
        return view('admin.pages.form', compact('page'));
    }

    public function update(Request $request, Page $page): RedirectResponse
    {
        $page->update($this->validated($request, $page));

        return redirect()->route('admin.pages.edit', $page)->with('status', "Page \"{$page->title}\" updated.");
    }

    public function destroy(Page $page): RedirectResponse
    {
        $page->delete();

        return redirect()->route('admin.pages.index')->with('status', "Page \"{$page->title}\" deleted.");
    }

    private function validated(Request $request, ?Page $page = null): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'slug' => ['nullable', 'alpha_dash', 'max:160', Rule::unique('pages')->ignore($page), Rule::notIn(['about', 'pricing', 'blog', 'contact', 'books', 'admin'])],
            'content' => ['nullable', 'string', 'max:100000'],
            'meta_description' => ['nullable', 'string', 'max:300'],
            'show_in_footer' => ['boolean'],
            'is_published' => ['boolean'],
        ], ['slug.not_in' => 'This web address is already used by another part of the store.']);
    }
}
