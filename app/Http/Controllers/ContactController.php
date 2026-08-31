<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;

class ContactController extends Controller
{
    public function store(ContactRequest $request): RedirectResponse
    {
        Log::info('Contact form submission', $request->validated());

        return back()->with('status', 'Thanks — your message has been noted. We’ll be in touch soon.');
    }
}
