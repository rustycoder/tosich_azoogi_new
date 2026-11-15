<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\LlmFeed;
use App\Services\Chat\ChatOrchestrator;
use Illuminate\Database\Seeder;

class AiChatRulesSyncSeeder extends Seeder
{
    /**
     * Sync updated AI chat rules, company context, FAQs, and widget branding defaults
     * into the database while preserving custom admin-added items.
     */
    public function run(): void
    {
        $salesEmail = (string) config('mail.sales_address', 'sales@azoogi.com');

        $this->syncRules($salesEmail);
        $this->syncContext();
        $this->syncFaqs();
        $this->syncWidgetBranding($salesEmail);
    }

    protected function syncRules(string $salesEmail): void
    {
        $defaultsById = [];
        foreach (ChatOrchestrator::getDefaultRulesItems() as $item) {
            $defaultsById[$item['id']] = $item;
        }

        $managedIds = ['rule_specs', 'rule_escalation', 'rule_brand_positioning', 'rule_scope_guardrail'];
        $existing = ChatOrchestrator::getRulesItems();
        $seenIds = [];
        $updated = [];

        foreach ($existing as $item) {
            if (! is_array($item)) {
                continue;
            }

            $id = (string) ($item['id'] ?? '');
            $seenIds[$id] = true;

            if (in_array($id, $managedIds, true) && isset($defaultsById[$id])) {
                $item = $defaultsById[$id];
            } elseif (isset($item['content']) && is_string($item['content'])) {
                $item['content'] = str_replace('sales@azoogi.com.au', $salesEmail, $item['content']);
            }

            $updated[] = $item;
        }

        foreach ($managedIds as $id) {
            if (empty($seenIds[$id]) && isset($defaultsById[$id])) {
                $updated[] = $defaultsById[$id];
            }
        }

        LlmFeed::query()->updateOrCreate(
            ['key' => 'ai_rules_items'],
            [
                'content' => json_encode(array_values($updated), JSON_PRETTY_PRINT),
                'is_custom' => true,
            ]
        );
    }

    protected function syncContext(): void
    {
        $defaultsById = [];
        foreach (ChatOrchestrator::getDefaultContextItems() as $item) {
            $defaultsById[$item['id']] = $item;
        }

        $managedIds = ['context_facility', 'context_dispatch'];
        $existing = ChatOrchestrator::getContextItems();
        $seenIds = [];
        $updated = [];

        foreach ($existing as $item) {
            if (! is_array($item)) {
                continue;
            }

            $id = (string) ($item['id'] ?? '');
            $seenIds[$id] = true;

            if (in_array($id, $managedIds, true) && isset($defaultsById[$id])) {
                $item = $defaultsById[$id];
            }

            $updated[] = $item;
        }

        foreach ($managedIds as $id) {
            if (empty($seenIds[$id]) && isset($defaultsById[$id])) {
                $updated[] = $defaultsById[$id];
            }
        }

        LlmFeed::query()->updateOrCreate(
            ['key' => 'ai_context_items'],
            [
                'content' => json_encode(array_values($updated), JSON_PRETTY_PRINT),
                'is_custom' => true,
            ]
        );
    }

    protected function syncFaqs(): void
    {
        $feed = LlmFeed::query()->where('key', 'ai_faqs')->first();
        if (! $feed || empty($feed->content)) {
            return;
        }

        $decoded = json_decode((string) $feed->content, true);
        if (! is_array($decoded)) {
            return;
        }

        $updated = array_map(function ($faq) {
            if (! is_array($faq)) {
                return $faq;
            }

            if (($faq['id'] ?? '') === 'faq_lead_times') {
                $faq['answer'] = 'Standard lead times for custom extrusion cutting, soldering, and testing are 3 to 5 business days for dispatch across Australia and the South Pacific.';
            }

            return $faq;
        }, $decoded);

        $feed->update([
            'content' => json_encode(array_values($updated), JSON_PRETTY_PRINT),
        ]);
    }

    protected function syncWidgetBranding(string $salesEmail): void
    {
        $feed = LlmFeed::query()->where('key', 'ai_widget_branding')->first();
        $existing = ($feed && ! empty($feed->content))
            ? json_decode((string) $feed->content, true)
            : [];

        if (! is_array($existing)) {
            $existing = [];
        }

        $defaults = ChatOrchestrator::getWidgetBranding();
        $merged = array_merge($defaults, array_filter($existing, fn ($v) => $v !== null && $v !== ''));
        $merged['sales_email'] = $salesEmail;

        LlmFeed::query()->updateOrCreate(
            ['key' => 'ai_widget_branding'],
            [
                'content' => json_encode($merged, JSON_PRETTY_PRINT),
                'is_custom' => true,
            ]
        );
    }
}
