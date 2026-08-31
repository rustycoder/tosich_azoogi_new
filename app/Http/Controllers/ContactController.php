<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ContactController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'your-name' => ['required', 'string', 'max:400'],
            'your-email' => ['required', 'email', 'max:400'],
            'your-company' => ['required', 'string', 'max:400'],
            'your-message' => ['required', 'string', 'max:2000'],
        ]);

        Log::info('Contact form submission', $validated);

        return back()->with('status', 'Thanks — your message has been noted. We’ll be in touch soon.');
    }
}
