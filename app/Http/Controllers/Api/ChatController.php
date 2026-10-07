<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChatSession;
use App\Models\Product;
use App\Services\Chat\ChatOrchestrator;
use App\Services\Chat\Tools\SendChatTranscriptToSalesTool;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function __construct(
        protected ChatOrchestrator $orchestrator
    ) {}

    public function init(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'session_uuid' => 'nullable|string|uuid',
            'referrer_url' => 'nullable|string|max:500',
            'name' => 'nullable|string|max:150',
            'email' => 'nullable|email|max:150',
            'project_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'company' => 'nullable|string|max:150',
        ]);

        $session = $this->orchestrator->getOrCreateSession(
            $validated['session_uuid'] ?? null,
            $validated['referrer_url'] ?? null,
            array_filter([
                'name' => $validated['name'] ?? null,
                'email' => $validated['email'] ?? null,
                'project_name' => $validated['project_name'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'company' => $validated['company'] ?? null,
            ]),
        );

        $greeting = $this->orchestrator->generatePersonalizedGreeting($session);
        $branding = ChatOrchestrator::getWidgetBranding();

        return response()->json([
            'status' => 'success',
            'session_uuid' => $session->uuid,
            'lead_name' => $session->lead_name,
            'lead_email' => $session->lead_email,
            'project_name' => $session->project_name,
            'greeting' => $greeting,
            'has_lead' => $session->hasLead(),
            'branding' => $branding,
        ]);
    }

    public function message(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => 'required|string|max:1500',
            'session_uuid' => 'nullable|string|uuid',
            'referrer_url' => 'nullable|string|max:500',
            'name' => 'nullable|string|max:150',
            'email' => 'nullable|email|max:150',
            'project_name' => 'nullable|string|max:255',
            'quote_items' => 'nullable|array',
        ]);

        if ($request->has('quote_items')) {
            $clientItems = (array) $request->input('quote_items', []);
            $syncedList = [];
            foreach ($clientItems as $it) {
                if (empty($it['id']) && empty($it['name'])) {
                    continue;
                }
                $id = (string) ($it['id'] ?? $it['name']);
                $syncedList[$id] = [
                    'id' => $it['id'] ?? null,
                    'name' => (string) ($it['name'] ?? ''),
                    'code' => (string) ($it['sku'] ?? $it['code'] ?? ''),
                    'quantity' => max(1, (int) ($it['qty'] ?? $it['quantity'] ?? 1)),
                    'image_url' => (string) ($it['image'] ?? $it['image_url'] ?? ''),
                    'url' => (string) ($it['url'] ?? ''),
                ];
            }
            session()->put('visitor_quote_list', $syncedList);
        }

        $session = $this->orchestrator->getOrCreateSession(
            $validated['session_uuid'] ?? null,
            $validated['referrer_url'] ?? null,
            array_filter([
                'name' => $validated['name'] ?? null,
                'email' => $validated['email'] ?? null,
                'project_name' => $validated['project_name'] ?? null,
            ]),
        );

        $result = $this->orchestrator->handleUserMessage($session, $validated['message']);
        $currentQuoteList = session()->get('visitor_quote_list', []);

        return response()->json([
            'status' => 'success',
            'session_uuid' => $result['session_uuid'],
            'reply' => $result['response'],
            'cards' => $result['cards'],
            'quote_count' => count($currentQuoteList),
            'quote_items' => array_values($currentQuoteList),
        ]);
    }

    public function session(string $uuid): JsonResponse
    {
        $session = ChatSession::where('uuid', $uuid)->with('messages')->first();

        if (! $session) {
            return response()->json([
                'status' => 'not_found',
                'messages' => [],
                'quote_count' => count(session()->get('visitor_quote_list', [])),
            ]);
        }

        $formattedMessages = $session->messages->map(function ($msg) {
            return [
                'id' => $msg->id,
                'sender' => $msg->sender,
                'content' => $msg->content,
                'cards' => $msg->cards_payload,
                'created_at' => $msg->created_at->toIso8601String(),
            ];
        });

        return response()->json([
            'status' => 'success',
            'session_uuid' => $session->uuid,
            'messages' => $formattedMessages,
            'quote_count' => count(session()->get('visitor_quote_list', [])),
        ]);
    }

    public function addQuote(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|integer',
            'quantity' => 'nullable|integer|min:1',
        ]);

        $product = Product::find($validated['product_id']);
        if (! $product) {
            return response()->json(['status' => 'error', 'message' => 'Product not found.'], 404);
        }

        $qty = (int) ($validated['quantity'] ?? 1);
        $quoteList = session()->get('visitor_quote_list', []);

        $coverUrl = null;
        if (! empty($product->cover)) {
            $coverUrl = str_starts_with($product->cover, 'http') ? $product->cover : asset($product->cover);
        }

        $currentQty = $quoteList[$product->id]['quantity'] ?? 0;
        $quoteList[$product->id] = [
            'id' => $product->id,
            'name' => $product->product_name,
            'code' => $product->product_code,
            'quantity' => $currentQty + $qty,
            'image_url' => $coverUrl,
            'url' => route('products.show', $product->slug ?: $product->id),
        ];

        session()->put('visitor_quote_list', $quoteList);

        return response()->json([
            'status' => 'success',
            'message' => "Added {$qty}x '{$product->product_name}' to your quote request.",
            'quote_count' => count($quoteList),
            'items' => array_values($quoteList),
            'quote_url' => route('request-a-quote'),
        ]);
    }

    public function clear(Request $request): JsonResponse
    {
        $uuid = $request->input('session_uuid');
        if (! empty($uuid)) {
            $session = ChatSession::where('uuid', $uuid)->first();
            if ($session) {
                $session->messages()->delete();
                $session->update(['messages_count' => 0, 'status' => 'active']);
            }
        }

        return response()->json(['status' => 'success', 'message' => 'Chat session reset.']);
    }

    public function sendTranscript(Request $request, SendChatTranscriptToSalesTool $tool): JsonResponse
    {
        $validated = $request->validate([
            'session_uuid' => 'required|string|uuid',
            'name' => 'nullable|string|max:150',
            'email' => 'nullable|email|max:150',
            'phone' => 'nullable|string|max:50',
            'company' => 'nullable|string|max:150',
            'project_name' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        $res = $tool->execute($validated);

        if (($res['result']['status'] ?? '') === 'success') {
            return response()->json([
                'status' => 'success',
                'message' => $res['result']['message'] ?? 'Transcript forwarded successfully to sales team.',
                'cards' => ! empty($res['cards']) ? [$res['cards']] : [],
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => $res['result']['message'] ?? 'Failed to send transcript.',
        ], 422);
    }
}
