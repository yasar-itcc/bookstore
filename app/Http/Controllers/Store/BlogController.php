<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BlogController extends Controller
{
    public function index(Request $request): View
    {
        $posts = Post::live()
            ->with('author')
            ->when($request->search, fn ($query, $search) => $query->where(fn ($q) => $q->where('title', 'like', "%{$search}%")->orWhere('excerpt', 'like', "%{$search}%")))
            ->when($request->category, fn ($query, $category) => $query->where('category', $category))
            ->paginate(8)
            ->withQueryString();

        return view('store.blog.index', ['posts' => $posts] + $this->sidebar());
    }

    public function show(string $slug): View
    {
        $post = Post::live()->with('author')->where('slug', $slug)->firstOrFail();

        return view('store.blog.show', [
            'post' => $post,
            'related' => Post::live()->whereKeyNot($post->id)->where('category', $post->category)->take(3)->get(),
        ] + $this->sidebar());
    }

    private function sidebar(): array
    {
        return [
            'blogCategories' => Post::live()->reorder()->whereNotNull('category')->selectRaw('category, COUNT(*) as total')->groupBy('category')->orderBy('category')->pluck('total', 'category'),
            'recent' => Post::live()->take(4)->get(),
        ];
    }
}
