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

    private const CHART_LEFT = 16.0;

    private const CHART_RIGHT = 744.0;

    private const CHART_TOP = 28.0;

    private const CHART_BOTTOM = 210.0;

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
        $enquiryPaths = $this->seriesPaths($enquiries, $max);
        $datasheetPaths = $this->seriesPaths($datasheets, $max);
        $span = 11;
        $months = [];

        foreach (self::MONTHS as $index => $label) {
            $months[] = [
                'label' => $label,
                'x' => round(self::CHART_LEFT + ((self::CHART_RIGHT - self::CHART_LEFT) * ($index / $span)), 1),
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
            'months' => $months,
            'ticks' => $ticks,
            'show_enquiries' => $showEnquiries,
            'show_datasheets' => $showDatasheets,
            'enquiries' => $enquiries,
            'datasheets' => $datasheets,
            'enquiry_line' => $enquiryPaths['line'],
            'enquiry_area' => $enquiryPaths['area'],
            'enquiry_points' => $enquiryPaths['points'],
            'datasheet_line' => $datasheetPaths['line'],
            'datasheet_area' => $datasheetPaths['area'],
            'datasheet_points' => $datasheetPaths['points'],
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
     * @return array{line: string, area: string, points: list<array{x: float, y: float, value: int}>}
     */
    private function seriesPaths(array $values, int $max): array
    {
        $points = [];
        $coords = [];
        $span = max(1, count($values) - 1);

        foreach ($values as $index => $value) {
            $x = round(self::CHART_LEFT + ((self::CHART_RIGHT - self::CHART_LEFT) * ($index / $span)), 1);
            $y = round(self::CHART_BOTTOM - (($value / $max) * (self::CHART_BOTTOM - self::CHART_TOP)), 1);
            $coords[] = [$x, $y];
            $points[] = [
                'x' => $x,
                'y' => $y,
                'value' => $value,
            ];
        }

        $line = $this->smoothLine($coords);
        $first = $coords[0];
        $last = $coords[array_key_last($coords)];

        return [
            'line' => $line,
            'area' => $line.' L '.$last[0].' '.self::CHART_BOTTOM.' L '.$first[0].' '.self::CHART_BOTTOM.' Z',
            'points' => $points,
        ];
    }

    /**
     * @param  list<array{0: float, 1: float}>  $points
     */
    private function smoothLine(array $points): string
    {
        $count = count($points);
        $line = 'M '.$points[0][0].' '.$points[0][1];

        for ($index = 0; $index < $count - 1; $index++) {
            $previous = $points[max(0, $index - 1)];
            $current = $points[$index];
            $next = $points[$index + 1];
            $after = $points[min($count - 1, $index + 2)];
            $controlX1 = round($current[0] + (($next[0] - $previous[0]) / 6), 1);
            $controlY1 = $this->clampChartY($current[1] + (($next[1] - $previous[1]) / 6));
            $controlX2 = round($next[0] - (($after[0] - $current[0]) / 6), 1);
            $controlY2 = $this->clampChartY($next[1] - (($after[1] - $current[1]) / 6));
            $line .= " C {$controlX1} {$controlY1} {$controlX2} {$controlY2} {$next[0]} {$next[1]}";
        }

        return $line;
    }

    private function clampChartY(float $y): float
    {
        return round(min(self::CHART_BOTTOM, max(self::CHART_TOP, $y)), 1);
    }
}
