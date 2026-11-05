<?php

declare(strict_types=1);

namespace App\Mcp\Tools\Backend;

use App\Enums\EnquiryStatus;
use App\Enums\EnquiryType;
use App\Mcp\Contracts\McpToolInterface;
use App\Models\Enquiry;
use App\Models\PageVisit;
use App\Models\ProductDatasheetExport;
use App\Models\User;
use App\Services\Contracts\IDashboardMetricsService;
use Illuminate\Support\Facades\DB;
use Throwable;

class DashboardAnalyticsTool implements McpToolInterface
{
    public function __construct(
        protected IDashboardMetricsService $metricsService,
    ) {}

    public function getName(): string
    {
        return 'backend_dashboard_analytics';
    }

    public function getDescription(): string
    {
        return 'Retrieves website traffic metrics, top visited pages, visitor countries, top viewed products, custom PDF datasheet download counts, and pending enquiry counts.';
    }

    /**
     * @return array<string, mixed>
     */
    public function getInputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'metric' => [
                    'type' => 'string',
                    'enum' => ['all', 'enquiries', 'traffic', 'datasheets'],
                    'description' => 'Specific analytics slice to retrieve (default: all).',
                ],
            ],
        ];
    }

    public function execute(array $arguments): array
    {
        $metric = (string) ($arguments['metric'] ?? 'all');
        $user = auth()->user() ?? User::query()->first();

        try {
            $result = [];

            if (in_array($metric, ['all', 'enquiries'], true)) {
                $result['enquiries_summary'] = [
                    'total' => Enquiry::count(),
                    'pending_quote' => Enquiry::where('type', EnquiryType::Quote)->where('status', EnquiryStatus::Pending)->count(),
                    'pending_product' => Enquiry::where('type', EnquiryType::Product)->where('status', EnquiryStatus::Pending)->count(),
                    'pending_contact' => Enquiry::where('type', EnquiryType::Contact)->where('status', EnquiryStatus::Pending)->count(),
                    'breakdown' => $user ? $this->metricsService->enquiries($user) : null,
                ];
            }

            if (in_array($metric, ['all', 'traffic'], true)) {
                $result['traffic_summary'] = [
                    'total_page_visits' => PageVisit::count(),
                    'visited_pages' => $user ? $this->metricsService->visitedPages($user) : null,
                    'visited_countries' => $user ? $this->metricsService->visitedCountries($user) : null,
                    'top_products' => $user ? $this->metricsService->topProducts($user) : null,
                ];
            }

            if (in_array($metric, ['all', 'datasheets'], true)) {
                $topExported = ProductDatasheetExport::query()
                    ->select('product_code', 'product_name', DB::raw('COUNT(*) as exports_count'))
                    ->groupBy('product_code', 'product_name')
                    ->orderByDesc('exports_count')
                    ->limit(10)
                    ->get();

                $result['datasheets_summary'] = [
                    'total_exports' => ProductDatasheetExport::count(),
                    'top_exported_products' => $topExported,
                    'breakdown' => $user ? $this->metricsService->datasheets($user) : null,
                ];
            }

            return [
                'isError' => false,
                'content' => json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
            ];
        } catch (Throwable $e) {
            return [
                'isError' => true,
                'content' => 'Dashboard analytics failed: '.$e->getMessage(),
            ];
        }
    }
}
