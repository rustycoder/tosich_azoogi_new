<?php

namespace App\Services\Contracts;

use App\Models\User;

interface IDashboardMetricsService
{
    /**
     * @return array{
     *     country: list<array{label: string, percent: int, color: string}>,
     *     device: list<array{label: string, percent: int, color: string}>
     * }|null
     */
    public function enquiries(User $user): ?array;

    /**
     * @return array{
     *     country: list<array{label: string, percent: int, color: string}>,
     *     device: list<array{label: string, percent: int, color: string}>
     * }|null
     */
    public function datasheets(User $user): ?array;

    /**
     * @return array{
     *     year: int,
     *     months: list<array{label: string, x: float}>,
     *     ticks: list<array{label: int, y: float}>,
     *     show_enquiries: bool,
     *     show_datasheets: bool,
     *     enquiries: list<int>,
     *     datasheets: list<int>,
     *     enquiry_line: string,
     *     enquiry_area: string,
     *     enquiry_points: list<array{x: float, y: float, value: int}>,
     *     datasheet_line: string,
     *     datasheet_area: string,
     *     datasheet_points: list<array{x: float, y: float, value: int}>
     * }|null
     */
    public function engagement(User $user): ?array;
}
