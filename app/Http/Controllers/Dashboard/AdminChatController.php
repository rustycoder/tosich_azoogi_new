<?php

declare(strict_types=1);

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\Chat\AdminChatOrchestrator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminChatController extends Controller
{
    public function __construct(
        protected AdminChatOrchestrator $orchestrator,
    ) {}

    public function init(Request $request): JsonResponse
    {
        $user = $request->user();
        $history = (array) $request->session()->get('admin_copilot_history', []);
        $cardsHistory = (array) $request->session()->get('admin_copilot_cards', []);

        return response()->json([
            'status' => 'success',
            'user_name' => $user?->name ?? 'Admin',
            'greeting' => 'Hi '.($user?->name ?? 'there').'! I am your Azoogi Admin Copilot powered by MCP. Ask me to inspect records, update page copy, check Airtable sync status, manage AI FAQs, or review pending enquiries.',
            'starter_chips' => AdminChatOrchestrator::getStarterChips(),
            'messages' => $this->formatSessionMessages($history, $cardsHistory),
        ]);
    }

    public function message(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
            'current_url' => ['nullable', 'string', 'max:500'],
        ]);

        $user = $request->user();
        $history = (array) $request->session()->get('admin_copilot_history', []);
        $cardsHistory = (array) $request->session()->get('admin_copilot_cards', []);

        $result = $this->orchestrator->handleAdminMessage(
            user: $user,
            message: $validated['message'],
            history: $history,
            currentUrl: $validated['current_url'] ?? $request->header('referer'),
        );

        $updatedHistory = $result['history'];
        $assistantIndex = count($updatedHistory) - 1;
        if (! empty($result['cards']) && $assistantIndex >= 0) {
            $cardsHistory[$assistantIndex] = $result['cards'];
        }

        $request->session()->put('admin_copilot_history', $updatedHistory);
        $request->session()->put('admin_copilot_cards', $cardsHistory);

        return response()->json([
            'status' => 'success',
            'reply' => $result['reply'],
            'cards' => $result['cards'],
            'tool_calls' => $result['tool_calls'],
        ]);
    }

    public function clear(Request $request): JsonResponse
    {
        $request->session()->forget(['admin_copilot_history', 'admin_copilot_cards']);

        return response()->json([
            'status' => 'success',
            'message' => 'Admin Copilot conversation cleared.',
        ]);
    }

    /**
     * @param  array<int, array{role: string, content: string}>  $history
     * @param  array<int, array<int, mixed>>  $cardsHistory
     * @return array<int, array{sender: string, content: string, cards: array<int, mixed>}>
     */
    protected function formatSessionMessages(array $history, array $cardsHistory): array
    {
        $formatted = [];
        foreach ($history as $idx => $item) {
            $formatted[] = [
                'sender' => ($item['role'] ?? 'user') === 'assistant' ? 'assistant' : 'user',
                'content' => (string) ($item['content'] ?? ''),
                'cards' => $cardsHistory[$idx] ?? [],
            ];
        }

        return $formatted;
    }
}
