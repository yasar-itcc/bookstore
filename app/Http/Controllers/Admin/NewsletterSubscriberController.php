<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class NewsletterSubscriberController extends Controller
{
    public function index(Request $request): View
    {
        $subscribers = NewsletterSubscriber::when($request->search, fn ($query, $search) => $query->where('email', 'like', "%{$search}%"))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $activeCount = NewsletterSubscriber::where('is_active', true)->count();

        return view('admin.subscribers.index', compact('subscribers', 'activeCount'));
    }

    /**
     * Download active subscribers as a CSV file for your email tool.
     */
    public function export(): StreamedResponse
    {
        return response()->streamDownload(function () {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['email', 'subscribed_on']);
            NewsletterSubscriber::where('is_active', true)->orderBy('id')->each(
                fn ($subscriber) => fputcsv($out, [$subscriber->email, $subscriber->created_at->toDateString()])
            );
            fclose($out);
        }, 'newsletter-subscribers-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv']);
    }

    public function update(Request $request, NewsletterSubscriber $subscriber): RedirectResponse
    {
        $subscriber->update(['is_active' => $request->boolean('is_active')]);

        return back()->with('status', "{$subscriber->email} ".($subscriber->is_active ? 'subscribed again.' : 'unsubscribed.'));
    }

    public function destroy(NewsletterSubscriber $subscriber): RedirectResponse
    {
        $subscriber->delete();

        return back()->with('status', "{$subscriber->email} deleted.");
    }
}
