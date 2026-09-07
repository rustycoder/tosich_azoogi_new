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
     *     plot_left: float,
     *     plot_right: float,
     *     months: list<array{label: string, x: float}>,
     *     ticks: list<array{label: int, y: float}>,
     *     show_enquiries: bool,
     *     show_datasheets: bool,
     *     enquiries: list<int>,
     *     datasheets: list<int>,
     *     enquiry_bars: list<array{x: float, y: float, width: float, height: float, value: int}>,
     *     datasheet_bars: list<array{x: float, y: float, width: float, height: float, value: int}>
     * }|null
     */
    public function engagement(User $user): ?array;

    /**
     * @return list<array{label: string, percent: int, color: string}>|null
     */
    public function visitedPages(User $user): ?array;

    /**
     * @return list<array{label: string, percent: int, color: string}>|null
     */
    public function visitedCountries(User $user): ?array;

    /**
     * @return list<array{label: string, sku: string, icon: string, url: string, color: string}>|null
     */
    public function topProducts(User $user): ?array;
}
