<?php

namespace App\Http\Controllers;

use App\Enums\EnquiryType;
use App\Http\Requests\ProductEnquiryRequest;
use App\Services\Contracts\IEnquiryService;
use Illuminate\Http\RedirectResponse;

class ProductEnquiryController extends Controller
{
    public function __construct(private IEnquiryService $enquiries) {}

    public function store(ProductEnquiryRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $this->enquiries->submit(EnquiryType::Product, [
            'name' => $data['quote-name'],
            'email' => $data['quote-email'],
            'company' => $data['quote-company'] ?? null,
            'message' => $data['quote-message'] ?? null,
            'payload' => [
                'project' => $data['quote-project'] ?? '',
                'specification' => $data['quote-spec'],
            ],
        ]);

        return back()->with('status', 'Thanks — your product enquiry has been noted. We’ll be in touch soon.');
    }
}
