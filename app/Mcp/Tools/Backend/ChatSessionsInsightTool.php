<?php

declare(strict_types=1);

namespace App\Mcp\Tools\Backend;

use App\Enums\EnquiryType;
use App\Mcp\Contracts\McpToolInterface;
use App\Models\ChatMessage;
use App\Models\ChatSession;
use App\Services\Contracts\IEnquiryService;
use Throwable;

class ChatSessionsInsightTool implements McpToolInterface
{
    public function __construct(
        protected IEnquiryService $enquiryService,
    ) {}

    public function getName(): string
    {
        return 'backend_chat_sessions_insight';
    }

    public function getDescription(): string
    {
        return 'Lists customer AI chat sessions, reads full conversation transcripts, toggles read/favorite flags, or converts a chat session into a formal quote enquiry.';
    }

    /**
     * @return array<string, mixed>
     */
    public function getInputSchema(): array
    {
        return [
            'type' => 'object',
            'required' => ['action'],
            'properties' => [
                'action' => [
                    'type' => 'string',
                    'enum' => ['list', 'transcript', 'toggle_read', 'toggle_favorite', 'convert_to_enquiry'],
                    'description' => 'Operation to perform on customer chat sessions.',
                ],
                'filter' => [
                    'type' => 'string',
                    'enum' => ['all', 'unread', 'favorites', 'leads'],
                    'description' => 'Filter when action is "list" (default: all).',
                ],
                'search' => [
                    'type' => 'string',
                    'description' => 'Optional search keyword across visitor name, email, project name, or summary.',
                ],
                'limit' => [
                    'type' => 'integer',
                    'description' => 'Maximum sessions to return (1-30, default: 10).',
                ],
                'session_id' => [
                    'type' => 'integer',
                    'description' => 'Target ChatSession numeric ID or use session_uuid.',
                ],
                'session_uuid' => [
                    'type' => 'string',
                    'description' => 'Target ChatSession UUID.',
                ],
            ],
        ];
    }

    public function execute(array $arguments): array
    {
        $action = (string) ($arguments['action'] ?? 'list');
        $user = auth()->user();

        if ($user && ! $user->isAdmin() && ! $user->canManageEnquiries() && ! $user->canManagePages()) {
            return [
                'isError' => true,
                'content' => 'You do not have permission to inspect or manage customer chat sessions.',
            ];
        }

        try {
            if ($action === 'list') {
                $limit = min(30, max(1, (int) ($arguments['limit'] ?? 10)));
                $filter = (string) ($arguments['filter'] ?? 'all');
                $search = trim((string) ($arguments['search'] ?? ''));

                $query = ChatSession::query()->latest('created_at');

                if ($filter === 'unread') {
                    $query->where('is_read', false);
                } elseif ($filter === 'favorites') {
                    $query->where('is_favorite', true);
                } elseif ($filter === 'leads') {
                    $query->where(function ($q) {
                        $q->whereNotNull('lead_email')
                            ->orWhereNotNull('lead_name')
                            ->orWhereNotNull('project_name')
                            ->orWhereNotNull('enquiry_id');
                    });
                }

                if ($search !== '') {
                    $query->where(function ($q) use ($search) {
                        $q->where('lead_name', 'like', "%{$search}%")
                            ->orWhere('lead_email', 'like', "%{$search}%")
                            ->orWhere('project_name', 'like', "%{$search}%")
                            ->orWhere('summary', 'like', "%{$search}%");
                    });
                }

                $sessions = $query->limit($limit)->get()->map(fn (ChatSession $s) => [
                    'id' => $s->id,
                    'uuid' => $s->uuid,
                    'lead_name' => $s->lead_name,
                    'lead_email' => $s->lead_email,
                    'project_name' => $s->project_name,
                    'country' => $s->country,
                    'messages_count' => $s->messages_count,
                    'is_read' => (bool) $s->is_read,
                    'is_favorite' => (bool) $s->is_favorite,
                    'enquiry_id' => $s->enquiry_id,
                    'summary' => $s->summary,
                    'total_tokens' => $s->total_tokens,
                    'total_cost' => $s->total_cost,
                    'created_at' => $s->created_at?->toIso8601String(),
                ]);

                return [
                    'isError' => false,
                    'content' => json_encode([
                        'metrics' => [
                            'total_sessions' => ChatSession::count(),
                            'unread_sessions' => ChatSession::where('is_read', false)->count(),
                            'favorite_sessions' => ChatSession::where('is_favorite', true)->count(),
                            'total_tokens' => (int) ChatSession::sum('total_tokens') ?: (int) ChatMessage::sum('tokens_used'),
                        ],
                        'sessions' => $sessions,
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
                ];
            }

            $session = $this->resolveSession($arguments);
            if (! $session) {
                return ['isError' => true, 'content' => 'ChatSession not found. Provide a valid session_id or session_uuid.'];
            }

            if ($action === 'transcript') {
                $session->load('messages');

                return [
                    'isError' => false,
                    'content' => json_encode([
                        'session' => [
                            'id' => $session->id,
                            'uuid' => $session->uuid,
                            'lead_name' => $session->lead_name,
                            'lead_email' => $session->lead_email,
                            'project_name' => $session->project_name,
                            'enquiry_id' => $session->enquiry_id,
                        ],
                        'messages' => $session->messages->map(fn (ChatMessage $m) => [
                            'id' => $m->id,
                            'sender' => $m->sender,
                            'content' => $m->content,
                            'created_at' => $m->created_at?->toIso8601String(),
                        ]),
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
                ];
            }

            if ($action === 'toggle_read') {
                $session->update(['is_read' => ! $session->is_read]);

                return [
                    'isError' => false,
                    'content' => json_encode([
                        'action' => 'toggle_read',
                        'session_id' => $session->id,
                        'is_read' => (bool) $session->is_read,
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
                ];
            }

            if ($action === 'toggle_favorite') {
                $session->update(['is_favorite' => ! $session->is_favorite]);

                return [
                    'isError' => false,
                    'content' => json_encode([
                        'action' => 'toggle_favorite',
                        'session_id' => $session->id,
                        'is_favorite' => (bool) $session->is_favorite,
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
                ];
            }

            if ($action === 'convert_to_enquiry') {
                if ($session->enquiry_id) {
                    return [
                        'isError' => false,
                        'content' => json_encode([
                            'action' => 'convert_to_enquiry',
                            'enquiry_id' => $session->enquiry_id,
                            'message' => "Session is already linked to Enquiry #{$session->enquiry_id}.",
                        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
                    ];
                }

                $session->load('messages');
                $name = $session->lead_name ?: 'Chat Visitor ('.($session->ip_address ?: 'unknown').')';
                $email = $session->lead_email ?: 'visitor-'.$session->uuid.'@chat.azoogi.com';
                $transcript = $session->messages->map(fn ($m) => ucfirst($m->sender).': '.$m->content)->implode("\n\n");

                $enquiry = $this->enquiryService->submit(EnquiryType::Quote, [
                    'name' => $name,
                    'email' => $email,
                    'phone' => $session->lead_phone,
                    'company' => $session->lead_company,
                    'message' => "Transcript:\n".$transcript,
                    'payload' => [
                        'source' => 'mcp_convert_to_enquiry',
                        'chat_session_uuid' => $session->uuid,
                        'project_name' => $session->project_name,
                    ],
                ]);

                $session->update(['enquiry_id' => $enquiry->id, 'status' => 'completed']);

                return [
                    'isError' => false,
                    'content' => json_encode([
                        'action' => 'convert_to_enquiry',
                        'session_id' => $session->id,
                        'enquiry_id' => $enquiry->id,
                        'message' => "Chat session successfully converted to Quote Enquiry #{$enquiry->id}.",
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
                ];
            }

            return ['isError' => true, 'content' => "Unsupported action: {$action}"];
        } catch (Throwable $e) {
            return [
                'isError' => true,
                'content' => 'Chat session tool error: '.$e->getMessage(),
            ];
        }
    }

    /**
     * @param  array<string, mixed>  $arguments
     */
    protected function resolveSession(array $arguments): ?ChatSession
    {
        if (! empty($arguments['session_id'])) {
            return ChatSession::find((int) $arguments['session_id']);
        }

        if (! empty($arguments['session_uuid'])) {
            return ChatSession::where('uuid', (string) $arguments['session_uuid'])->first();
        }

        return null;
    }
}
