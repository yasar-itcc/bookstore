<?php

namespace App\Http\Controllers\Admin;

use App\Enums\BookFormat as Format;
use App\Http\Controllers\Controller;
use App\Models\Author;
use App\Models\Book;
use App\Models\BookFormat;
use App\Models\Category;
use App\Models\Publisher;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $stats = [
            'books' => Book::count(),
            'activeBooks' => Book::where('is_active', true)->count(),
            'categories' => Category::count(),
            'authors' => Author::count(),
            'publishers' => Publisher::count(),
            'ebooks' => BookFormat::where('format', Format::Ebook->value)->count(),
            'unitsInStock' => (int) BookFormat::physical()->sum('stock'),
            'lowStock' => BookFormat::lowStock()->count(),
            'outOfStock' => BookFormat::outOfStock()->count(),
        ];

        // Stock value at selling price, physical formats only.
        $stats['stockValue'] = (float) BookFormat::physical()->selectRaw('COALESCE(SUM(price * stock), 0) as total')->value('total');

        $formatCounts = BookFormat::selectRaw('format, COUNT(*) as total')->groupBy('format')->pluck('total', 'format');

        $topCategories = Category::withCount('books')
            ->orderByDesc('books_count')
            ->take(6)
            ->get();

        $lowStockItems = BookFormat::with('book')
            ->physical()
            ->where('stock', '<=', BookFormat::LOW_STOCK_THRESHOLD)
            ->orderBy('stock')
            ->take(6)
            ->get();

        $recentBooks = Book::with(['category', 'authors', 'formats'])
            ->latest()
            ->take(6)
            ->get();

        return view('admin.dashboard', compact('stats', 'formatCounts', 'topCategories', 'lowStockItems', 'recentBooks'));
    }
}
