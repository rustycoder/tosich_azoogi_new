<?php

namespace App\Repositories\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait CountsByMonth
{
    /**
     * @return array<int, int>
     */
    private function countsByMonth(Builder $query, int $year): array
    {
        $month = $query->getConnection()->getDriverName() === 'sqlite'
            ? "CAST(strftime('%m', created_at) AS INTEGER)"
            : 'MONTH(created_at)';

        $rows = $query
            ->whereYear('created_at', $year)
            ->selectRaw($month.' as month, count(*) as total')
            ->groupByRaw($month)
            ->pluck('total', 'month');

        $counts = array_fill(1, 12, 0);

        foreach ($rows as $monthNumber => $total) {
            $counts[(int) $monthNumber] = (int) $total;
        }

        return $counts;
    }
}
