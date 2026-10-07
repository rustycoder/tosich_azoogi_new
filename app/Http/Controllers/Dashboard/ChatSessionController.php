<?php

declare(strict_types=1);

namespace App\Http\Controllers\Dashboard;

use App\Enums\EnquiryType;
use App\Http\Controllers\Controller;
use App\Models\ChatMessage;
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
                    ->orWhereNotNull('project_name')
                    ->orWhereNotNull('enquiry_id');
            });
        }

        // Search keyword
        if ($request->filled('q')) {
            $search = trim((string) $request->input('q'));
            $query->where(function ($q) use ($search) {
                $q->where('lead_name', 'like', "%{$search}%")
                    ->orWhere('lead_email', 'like', "%{$search}%")
                    ->orWhere('project_name', 'like', "%{$search}%")
                    ->orWhere('lead_company', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%")
                    ->orWhere('country', 'like', "%{$search}%")
                    ->orWhere('summary', 'like', "%{$search}%");
            });
        }

        // Sort options
        $currentSort = (string) $request->input('sort', 'latest');
        if ($currentSort === 'highest_cost') {
            $query->reorder('total_cost', 'desc');
        } elseif ($currentSort === 'highest_tokens') {
            $query->reorder('total_tokens', 'desc');
        } elseif ($currentSort === 'most_messages') {
            $query->reorder('messages_count', 'desc');
        }

        $sessions = $query->paginate(20)->withQueryString();

        $totalConversations = ChatSession::count();
        $totalTokens = (int) ChatSession::sum('total_tokens') ?: (int) ChatMessage::sum('tokens_used');
        $totalCost = (float) ChatSession::sum('total_cost') ?: (float) ChatMessage::sum('estimated_cost');
        $avgCost = $totalConversations > 0 ? ($totalCost / $totalConversations) : 0.0;

        $metrics = [
            'total_conversations' => $totalConversations,
            'leads_captured' => ChatSession::where(function ($q) {
                $q->whereNotNull('lead_email')
                    ->orWhereNotNull('lead_name')
                    ->orWhereNotNull('project_name')
                    ->orWhereNotNull('enquiry_id');
            })->count(),
            'active_today' => ChatSession::whereDate('created_at', today())->count(),
            'total_messages' => (int) ChatSession::sum('messages_count'),
            'total_tokens' => $totalTokens,
            'total_cost' => $totalCost,
            'avg_cost' => $avgCost,
        ];

        return view('dashboard.chat.index', [
            'sessions' => $sessions,
            'metrics' => $metrics,
            'currentStatus' => $request->input('status'),
            'searchQuery' => $request->input('q'),
            'hasLead' => $request->boolean('has_lead'),
            'currentSort' => $currentSort,
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
                'project_name' => $chatSession->project_name,
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
