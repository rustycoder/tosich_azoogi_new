<?php

namespace App\Http\Controllers\Dashboard;

use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Models\LlmFeed;
use App\Models\Product;
use App\Models\Project;
use App\Support\LlmsTxtBuilder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LlmFeedController extends Controller
{
    public function __construct(
        private LlmsTxtBuilder $builder,
    ) {}

    public function index(): View
    {
        $summaryFeed = LlmFeed::query()->where('key', LlmsTxtBuilder::FEED_KEY_SUMMARY)->first();
        $defaultSummary = $this->builder->buildDefaultSummaryMarkdown();
        $currentSummary = ($summaryFeed && ! empty($summaryFeed->content))
            ? (string) $summaryFeed->content
            : $defaultSummary;
        $isCustom = (bool) ($summaryFeed?->is_custom ?? false);

        $liveSummary = $this->builder->buildSummaryMarkdown();
        $liveFull = $this->builder->buildFullMarkdown();

        $productsCount = Product::query()
            ->where(function ($query) {
                $query->whereNull('status')
                    ->orWhere('status', 'publish')
                    ->orWhere('status', 'Publish');
            })
            ->where(function ($query) {
                $query->where(function ($q) {
                    $q->whereNotNull('slug')->where('slug', '!=', '');
                })->orWhere(function ($q) {
                    $q->whereNotNull('airtable_id')->where('airtable_id', '!=', '');
                });
            })
            ->count();

        $projectsCount = Project::query()
            ->where('status', Status::Active)
            ->whereNotNull('slug')
            ->where('slug', '!=', '')
            ->count();

        return view('dashboard.llms.index', [
            'summaryFeed' => $summaryFeed,
            'summaryContent' => $currentSummary,
            'defaultSummary' => $defaultSummary,
            'isCustom' => $isCustom,
            'liveSummary' => $liveSummary,
            'liveFull' => $liveFull,
            'productsCount' => $productsCount,
            'projectsCount' => $projectsCount,
            'lastUpdated' => $summaryFeed?->updated_at,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'content' => ['nullable', 'string'],
            'is_custom' => ['nullable', 'boolean'],
        ]);

        $content = trim((string) ($validated['content'] ?? ''));
        $isCustom = $request->boolean('is_custom');

        LlmFeed::query()->updateOrCreate(
            ['key' => LlmsTxtBuilder::FEED_KEY_SUMMARY],
            [
                'content' => $content !== '' ? $content : null,
                'is_custom' => $isCustom,
            ]
        );

        LlmsTxtBuilder::clearCache();
        $this->builder->getCachedSummary();
        $this->builder->getCachedFull();

        return redirect()
            ->route('dashboard.llms.index')
            ->with('status', 'LLM feed configuration saved and cache refreshed successfully.');
    }

    public function reset(): RedirectResponse
    {
        $defaultSummary = $this->builder->buildDefaultSummaryMarkdown();

        LlmFeed::query()->updateOrCreate(
            ['key' => LlmsTxtBuilder::FEED_KEY_SUMMARY],
            [
                'content' => $defaultSummary,
                'is_custom' => false,
            ]
        );

        LlmsTxtBuilder::clearCache();
        $this->builder->getCachedSummary();
        $this->builder->getCachedFull();

        return redirect()
            ->route('dashboard.llms.index')
            ->with('status', 'LLM summary feed has been reset to the default curated template.');
    }

    public function generate(): RedirectResponse
    {
        LlmsTxtBuilder::clearCache();
        $this->builder->getCachedSummary();
        $this->builder->getCachedFull();

        return redirect()
            ->route('dashboard.llms.index')
            ->with('status', 'All LLM and GEO feeds (/llms.txt and /llms-full.txt) regenerated successfully.');
    }
}
