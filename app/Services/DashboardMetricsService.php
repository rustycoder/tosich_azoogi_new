<?php

namespace App\Services;

use App\Enums\EnquiryType;
use App\Models\User;
use App\Repositories\Contracts\IEnquiryRepository;
use App\Repositories\Contracts\IProductDatasheetRepository;
use App\Services\Contracts\IDashboardMetricsService;
use Illuminate\Support\Collection;

class DashboardMetricsService implements IDashboardMetricsService
{
    private const LIMIT = 10;

    /**
     * @var list<string>
     */
    private const COLORS = [
        '#2d7a1e',
        '#3aa028',
        '#4eae3a',
        '#68bc56',
        '#82c972',
        '#9ad48c',
        '#6f9468',
        '#587a52',
        '#7fa078',
        '#a3b89e',
    ];

    /**
     * @var list<string>
     */
    private const MONTHS = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

    private const CHART_LEFT = 40.0;

    private const CHART_RIGHT = 752.0;

    private const CHART_TOP = 10.0;

    private const CHART_BOTTOM = 142.0;

    public function __construct(
        private IEnquiryRepository $enquiries,
        private IProductDatasheetRepository $datasheets,
    ) {}

    public function enquiries(User $user): ?array
    {
        $types = array_values(array_filter(
            EnquiryType::cases(),
            fn (EnquiryType $type): bool => $user->canManageEnquiryType($type),
        ));

        if ($types === []) {
            return null;
        }

        return $this->present($this->enquiries->originBuckets($types));
    }

    public function datasheets(User $user): ?array
    {
        if (! $user->canManageDatasheets()) {
            return null;
        }

        return $this->present($this->datasheets->originBuckets());
    }

    public function engagement(User $user): ?array
    {
        $showEnquiries = $user->canManageEnquiries();
        $showDatasheets = $user->canManageDatasheets();

        if (! $showEnquiries && ! $showDatasheets) {
            return null;
        }

        $year = now()->year;
        $enquiries = array_fill(0, 12, 0);
        $datasheets = array_fill(0, 12, 0);

        if ($showEnquiries) {
            $types = array_values(array_filter(
                EnquiryType::cases(),
                fn (EnquiryType $type): bool => $user->canManageEnquiryType($type),
            ));

            foreach ($this->enquiries->monthlyCounts($types, $year) as $month => $count) {
                $enquiries[$month - 1] = $count;
            }
        }

        if ($showDatasheets) {
            foreach ($this->datasheets->monthlyCounts($year) as $month => $count) {
                $datasheets[$month - 1] = $count;
            }
        }

        $max = max(1, ...$enquiries, ...$datasheets);
        $seriesCount = ($showEnquiries ? 1 : 0) + ($showDatasheets ? 1 : 0);
        $enquiryBars = $showEnquiries
            ? $this->columnBars($enquiries, $max, 0, $seriesCount)
            : [];
        $datasheetBars = $showDatasheets
            ? $this->columnBars($datasheets, $max, $showEnquiries ? 1 : 0, $seriesCount)
            : [];
        $groupWidth = (self::CHART_RIGHT - self::CHART_LEFT) / 12;
        $months = [];

        foreach (self::MONTHS as $index => $label) {
            $months[] = [
                'label' => $label,
                'x' => round(self::CHART_LEFT + ($groupWidth * ($index + 0.5)), 1),
            ];
        }

        $ticks = [];

        foreach (array_values(array_unique([0, (int) ceil($max / 2), $max])) as $value) {
            $ticks[] = [
                'label' => $value,
                'y' => round(self::CHART_BOTTOM - (($value / $max) * (self::CHART_BOTTOM - self::CHART_TOP)), 1),
            ];
        }

        return [
            'year' => $year,
            'plot_left' => self::CHART_LEFT,
            'plot_right' => self::CHART_RIGHT,
            'months' => $months,
            'ticks' => $ticks,
            'show_enquiries' => $showEnquiries,
            'show_datasheets' => $showDatasheets,
            'enquiries' => $enquiries,
            'datasheets' => $datasheets,
            'enquiry_bars' => $enquiryBars,
            'datasheet_bars' => $datasheetBars,
        ];
    }

    /**
     * @param  array{countries: Collection<int, object>, user_agents: Collection<int, object>}  $buckets
     * @return array{
     *     country: list<array{label: string, percent: int, color: string}>,
     *     device: list<array{label: string, percent: int, color: string}>
     * }
     */
    private function present(array $buckets): array
    {
        return [
            'country' => $this->rows($buckets['countries'], function (?string $key): string {
                $name = country_name($key);

                return $name !== '' ? $name : 'Unknown';
            }),
            'device' => $this->rows($buckets['user_agents'], function (?string $key): string {
                $name = device_name($key);

                return $name !== '' ? $name : 'Unknown';
            }),
        ];
    }

    /**
     * @param  Collection<int, object{origin_key: mixed, total: mixed}>  $buckets
     * @param  callable(?string): string  $label
     * @return list<array{label: string, percent: int, color: string}>
     */
    private function rows(Collection $buckets, callable $label): array
    {
        $grouped = [];

        foreach ($buckets as $bucket) {
            $name = $label(is_string($bucket->origin_key) ? $bucket->origin_key : null);
            $grouped[$name] = ($grouped[$name] ?? 0) + (int) $bucket->total;
        }

        arsort($grouped);

        $grouped = array_slice($grouped, 0, self::LIMIT, true);
        $shown = array_sum($grouped);

        if ($shown === 0) {
            return [];
        }

        $parts = [];

        foreach ($grouped as $name => $count) {
            $exact = ($count / $shown) * 100;
            $parts[] = [
                'label' => $name,
                'floor' => (int) floor($exact),
                'frac' => $exact - floor($exact),
            ];
        }

        $remaining = 100 - array_sum(array_column($parts, 'floor'));
        $order = array_keys($parts);
        usort($order, function (int $left, int $right) use ($parts): int {
            return $parts[$right]['frac'] <=> $parts[$left]['frac'] ?: $left <=> $right;
        });

        foreach ($order as $index) {
            if ($remaining <= 0) {
                break;
            }

            $parts[$index]['floor']++;
            $remaining--;
        }

        $rows = [];

        foreach ($parts as $index => $part) {
            $rows[] = [
                'label' => $part['label'],
                'percent' => $part['floor'],
                'color' => self::COLORS[$index],
            ];
        }

        return $rows;
    }

    /**
     * @param  list<int>  $values
     * @return list<array{x: float, y: float, width: float, height: float, value: int}>
     */
    private function columnBars(array $values, int $max, int $seriesIndex, int $seriesCount): array
    {
        $groupWidth = (self::CHART_RIGHT - self::CHART_LEFT) / 12;
        $pad = $groupWidth * 0.2;
        $gap = $seriesCount > 1 ? 2.4 : 0.0;
        $barWidth = round(($groupWidth - (2 * $pad) - (($seriesCount - 1) * $gap)) / max(1, $seriesCount), 1);
        $range = self::CHART_BOTTOM - self::CHART_TOP;
        $bars = [];

        foreach ($values as $index => $value) {
            $height = $value > 0 ? round(max(2.5, ($value / $max) * $range), 1) : 0.0;

            $bars[] = [
                'x' => round(self::CHART_LEFT + ($index * $groupWidth) + $pad + ($seriesIndex * ($barWidth + $gap)), 1),
                'y' => round(self::CHART_BOTTOM - $height, 1),
                'width' => $barWidth,
                'height' => $height,
                'value' => $value,
            ];
        }

        return $bars;
    }
}
