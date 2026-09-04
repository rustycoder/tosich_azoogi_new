<?php

namespace App\Http\Controllers\Dashboard;

use App\Enums\EnquiryStatus;
use App\Enums\EnquiryType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\UpdateEnquiryStatusRequest;
use App\Models\Enquiry;
use App\Services\Contracts\IEnquiryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EnquiryController extends Controller
{
    public function __construct(private IEnquiryService $enquiries) {}

    public function index(Request $request, ?string $type = null): View
    {
        $user = $request->user();
        $type ??= collect(EnquiryType::cases())
            ->first(fn (EnquiryType $enquiryType): bool => $user?->canManageEnquiryType($enquiryType) ?? false)
            ?->menuSlug() ?? 'quote';

        abort_unless(in_array($type, ['quote', 'products', 'contacts'], true), 404);

        $enquiryType = EnquiryType::fromMenuSlug($type);

        abort_unless($user?->canManageEnquiryType($enquiryType) ?? false, 403);

        return view('dashboard.enquiries.index', [
            'type' => $enquiryType,
            'columns' => $this->enquiries->kanban($enquiryType),
            'statuses' => EnquiryStatus::cases(),
        ]);
    }

    public function updateStatus(UpdateEnquiryStatusRequest $request, Enquiry $enquiry): JsonResponse
    {
        $enquiry = $this->enquiries->updateStatus(
            $enquiry,
            EnquiryStatus::from($request->validated('status')),
        )->load('updater:id,name');

        $updatedAt = $enquiry->updated_at?->timezone(config('app.timezone'));

        return response()->json([
            'status' => $enquiry->status->value,
            'label' => $enquiry->status->label(),
            'updated_by' => $enquiry->updater?->name,
            'updated_at' => $updatedAt?->format('j M Y, g:i A'),
            'updated_at_iso' => $enquiry->updated_at?->toIso8601String(),
            'message' => 'Enquiry marked '.$enquiry->status->label().'.',
        ]);
    }

    public function destroy(Request $request, Enquiry $enquiry): JsonResponse
    {
        abort_unless($request->user()?->canManageEnquiryType($enquiry->type) ?? false, 403);

        $this->enquiries->delete($enquiry);

        return response()->json([
            'message' => 'Enquiry deleted.',
        ]);
    }
}
