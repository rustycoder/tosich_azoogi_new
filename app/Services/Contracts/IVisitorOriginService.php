<?php

namespace App\Services\Contracts;

use Illuminate\Http\Request;

interface IVisitorOriginService
{
    /**
     * @return array{ip_address: ?string, country: ?string, user_agent: ?string}
     */
    public function capture(?Request $request = null): array;

    /**
     * IP, user agent, and country from CDN headers only — never looks up ipwho.is.
     *
     * @return array{ip_address: ?string, country: ?string, user_agent: ?string}
     */
    public function captureVisit(?Request $request = null): array;

    public function captureCountryFromHeaders(?Request $request = null): ?string;
}
