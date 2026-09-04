<?php

namespace App\Http\Controllers\Dashboard;

use App\Enums\EnquiryStatus;
use App\Enums\EnquiryType;
use App\Http\Controllers\Controller;
use App\Services\Contracts\IEnquiryService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private IEnquiryService $enquiries) {}

    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $boards = [];

        foreach (EnquiryType::cases() as $type) {
            if (! $user?->canManageEnquiryType($type)) {
                continue;
            }

            $boards[] = [
                'type' => $type,
                'enquiries' => $this->enquiries->kanban($type, EnquiryStatus::Pending)[EnquiryStatus::Pending->value],
            ];
        }

        return view('dashboard.home', [
            'pendingBoards' => $boards,
            'pendingStatus' => EnquiryStatus::Pending,
        ]);
    }
}
