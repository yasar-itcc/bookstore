<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PostController extends Controller
{
    public function index(Request $request): View
    {
        $posts = Post::with('author')
            ->when($request->search, fn ($query, $search) => $query->where('title', 'like', "%{$search}%"))
            ->latest('published_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.posts.index', compact('posts'));
    }

    public function create(): View
    {
        return view('admin.posts.form', ['post' => new Post(['is_published' => true, 'published_at' => now()]), 'categories' => $this->categories()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $post = Post::create($this->validated($request) + ['user_id' => $request->user()->id]);

        return redirect()->route('admin.posts.edit', $post)->with('status', "Post \"{$post->title}\" created.");
    }

    public function edit(Post $post): View
    {
        return view('admin.posts.form', ['post' => $post, 'categories' => $this->categories()]);
    }

    public function update(Request $request, Post $post): RedirectResponse
    {
        $post->update($this->validated($request, $post));

        return redirect()->route('admin.posts.edit', $post)->with('status', "Post \"{$post->title}\" updated.");
    }

    public function destroy(Post $post): RedirectResponse
    {
        if ($post->cover_image) {
            Storage::disk('public')->delete($post->cover_image);
        }
        $post->delete();

        return redirect()->route('admin.posts.index')->with('status', "Post \"{$post->title}\" deleted.");
    }

    private function validated(Request $request, ?Post $post = null): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'slug' => ['nullable', 'alpha_dash', 'max:220', Rule::unique('posts')->ignore($post)],
            'category' => ['nullable', 'string', 'max:60'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'content' => ['nullable', 'string', 'max:100000'],
            'meta_description' => ['nullable', 'string', 'max:300'],
            'is_published' => ['boolean'],
            'published_at' => ['nullable', 'date'],
            'cover_image' => ['nullable', 'image', 'max:4096'],
            'remove_cover' => ['boolean'],
        ]);

        $data['published_at'] ??= now();

        if ($request->hasFile('cover_image') || $request->boolean('remove_cover')) {
            if ($post?->cover_image) {
                Storage::disk('public')->delete($post->cover_image);
            }
            $data['cover_image'] = $request->file('cover_image')?->store('posts', 'public');
        }
        unset($data['remove_cover']);

        return $data;
    }

    /**
     * Categories already used, offered as suggestions in the form.
     */
    private function categories(): array
    {
        return Post::whereNotNull('category')->distinct()->orderBy('category')->pluck('category')->all();
    }
}
