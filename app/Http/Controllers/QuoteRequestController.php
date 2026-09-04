<?php

namespace App\Http\Controllers;

use App\Http\Requests\QuoteRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;

class QuoteRequestController extends Controller
{
    public function store(QuoteRequest $request): RedirectResponse
    {
        Log::info('Quote request submission', $request->validated());

        return back()->with('status', 'Thanks — your quote request has been noted. We’ll be in touch soon.');
    }
}
