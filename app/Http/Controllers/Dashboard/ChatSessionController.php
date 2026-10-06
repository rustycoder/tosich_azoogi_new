<?php

declare(strict_types=1);

namespace App\Http\Controllers\Dashboard;

use App\Enums\EnquiryType;
use App\Http\Controllers\Controller;
use App\Models\ChatSession;
use App\Services\Contracts\IEnquiryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChatSessionController extends Controller
{
    public function __construct(
        protected IEnquiryService $enquiryService
    ) {}

    public function index(Request $request): View
    {
        $query = ChatSession::query()->withCount('messages')->latest('updated_at');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // Filter by leads only
        if ($request->boolean('has_lead')) {
            $query->where(function ($q) {
                $q->whereNotNull('lead_email')
                    ->orWhereNotNull('lead_name')
                    ->orWhereNotNull('enquiry_id');
            });
        }

        // Search keyword
        if ($request->filled('q')) {
            $search = trim((string) $request->input('q'));
            $query->where(function ($q) use ($search) {
                $q->where('lead_name', 'like', "%{$search}%")
                    ->orWhere('lead_email', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%")
                    ->orWhere('country', 'like', "%{$search}%")
                    ->orWhere('summary', 'like', "%{$search}%");
            });
        }

        $sessions = $query->paginate(20)->withQueryString();

        $metrics = [
            'total_conversations' => ChatSession::count(),
            'leads_captured' => ChatSession::whereNotNull('lead_email')->orWhereNotNull('enquiry_id')->count(),
            'active_today' => ChatSession::whereDate('created_at', today())->count(),
            'total_messages' => ChatSession::sum('messages_count'),
        ];

        return view('dashboard.chat.index', [
            'sessions' => $sessions,
            'metrics' => $metrics,
            'currentStatus' => $request->input('status'),
            'searchQuery' => $request->input('q'),
            'hasLead' => $request->boolean('has_lead'),
        ]);
    }

    public function show(ChatSession $chatSession): View
    {
        $chatSession->load(['messages', 'enquiry']);

        return view('dashboard.chat.show', [
            'session' => $chatSession,
            'messages' => $chatSession->messages,
        ]);
    }

    public function convertToEnquiry(Request $request, ChatSession $chatSession): RedirectResponse
    {
        if ($chatSession->enquiry_id) {
            return back()->with('info', 'This chat session is already linked to Enquiry #'.$chatSession->enquiry_id);
        }

        $name = $chatSession->lead_name ?: 'Chat Visitor ('.$chatSession->ip_address.')';
        $email = $chatSession->lead_email ?: 'visitor-'.$chatSession->uuid.'@chat.azoogi.com';

        $transcript = $chatSession->messages->map(function ($m) {
            return ucfirst($m->sender).': '.$m->content;
        })->implode("\n\n");

        $enquiry = $this->enquiryService->submit(EnquiryType::Quote, [
            'name' => $name,
            'email' => $email,
            'phone' => $chatSession->lead_phone,
            'company' => $chatSession->lead_company,
            'message' => "Transcript:\n".$transcript,
            'payload' => [
                'source' => 'chat_session_manual_convert',
                'chat_session_uuid' => $chatSession->uuid,
            ],
        ]);

        $chatSession->update(['enquiry_id' => $enquiry->id, 'status' => 'completed']);

        return back()->with('success', 'Conversation successfully converted to Enquiry #'.$enquiry->id);
    }

    public function destroy(ChatSession $chatSession): JsonResponse
    {
        $chatSession->delete();

        return response()->json(['message' => 'Chat session deleted.']);
    }
}
