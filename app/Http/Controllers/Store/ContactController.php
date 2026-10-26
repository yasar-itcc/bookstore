<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function create(): View
    {
        return view('store.contact');
    }

    public function store(Request $request): RedirectResponse
    {
        // "website" is a hidden trap field: people never fill it, spam bots do.
        if (filled($request->website)) {
            return redirect()->route('contact')->with('status', 'Thanks! Your message has been sent.');
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150'],
            'phone' => ['nullable', 'string', 'max:30'],
            'subject' => ['nullable', 'string', 'max:150'],
            'message' => ['required', 'string', 'min:10', 'max:5000'],
        ]);

        ContactMessage::create($data + ['ip_address' => $request->ip()]);

        return redirect()->route('contact')->with('status', 'Thanks! Your message has been sent. We usually reply within one working day.');
    }
}
