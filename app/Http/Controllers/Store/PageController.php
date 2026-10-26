<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Page;
use App\Models\PricingPlan;
use App\Models\Testimonial;
use Illuminate\View\View;

class PageController extends Controller
{
    public function about(): View
    {
        return view('store.about', [
            'covers' => Book::where('is_active', true)->whereNotNull('cover_image')->orderByDesc('is_featured')->latest()->take(6)->get(),
            'testimonials' => Testimonial::where('is_active', true)->orderBy('sort_order')->take(3)->get(),
        ]);
    }

    public function pricing(): View
    {
        return view('store.pricing', ['plans' => PricingPlan::where('is_active', true)->orderBy('sort_order')->orderBy('price')->get()]);
    }

    public function show(string $slug): View
    {
        $page = Page::where('slug', $slug)->where('is_published', true)->firstOrFail();

        return view('store.page', compact('page'));
    }
}
