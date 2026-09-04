<?php

namespace App\Http\Controllers;

use App\Enums\EnquiryType;
use App\Http\Requests\ContactRequest;
use App\Services\Contracts\IEnquiryService;
use Illuminate\Http\RedirectResponse;

class ContactController extends Controller
{
    public function __construct(private IEnquiryService $enquiries) {}

    public function store(ContactRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $this->enquiries->submit(EnquiryType::Contact, [
            'name' => $data['your-name'],
            'email' => $data['your-email'],
            'company' => $data['your-company'],
            'message' => $data['your-message'],
        ]);

        return back()->with('status', 'Thanks — your message has been noted. We’ll be in touch soon.');
    }
}
