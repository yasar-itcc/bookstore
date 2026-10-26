<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NewsletterController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validateWithBag('newsletter', [
            'email' => ['required', 'email', 'max:150'],
        ]);

        $subscriber = NewsletterSubscriber::firstOrNew(['email' => strtolower($data['email'])]);
        $subscriber->fill(['is_active' => true, 'ip_address' => $subscriber->ip_address ?? $request->ip()])->save();

        return back()->with('newsletter', 'You\'re subscribed! Look out for new arrivals and offers in your inbox.')->withFragment('newsletter');
    }
}
