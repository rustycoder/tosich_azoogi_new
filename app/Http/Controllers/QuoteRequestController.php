<?php

namespace App\Http\Controllers;

use App\Enums\EnquiryType;
use App\Http\Requests\QuoteRequest;
use App\Services\Contracts\IEnquiryService;
use Illuminate\Http\RedirectResponse;

class QuoteRequestController extends Controller
{
    public function __construct(private IEnquiryService $enquiries) {}

    public function store(QuoteRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $this->enquiries->submit(EnquiryType::Quote, [
            'name' => $data['your-name'],
            'email' => $data['your-email'],
            'phone' => $data['your-phone'],
            'message' => $data['your-description'] ?? null,
            'payload' => [
                'description' => $data['your-description'] ?? '',
                'products' => $data['your-products'],
                'role' => $data['radio-choice'],
                'method' => $data['contact-choice'],
                'suburb' => $data['suburb-retailer'] ?? '',
            ],
        ]);

        return back()->with([
            'status' => 'Thanks — your quote request has been noted. We’ll be in touch soon.',
            'clear_quote' => true,
        ]);
    }
}
