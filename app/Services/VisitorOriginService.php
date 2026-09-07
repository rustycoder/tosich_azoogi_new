<?php

namespace App\Services;

use App\Services\Contracts\IVisitorOriginService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Throwable;

class VisitorOriginService implements IVisitorOriginService
{
    /**
     * @var list<string>
     */
    private const IP_HEADERS = [
        'CF-Connecting-IP',
        'True-Client-IP',
        'X-Real-IP',
        'X-Forwarded-For',
    ];

    /**
     * @var list<string>
     */
    private const COUNTRY_HEADERS = [
        'CF-IPCountry',
        'CloudFront-Viewer-Country',
        'X-AppEngine-Country',
        'X-Country-Code',
    ];

    /**
     * @var list<string>
     */
    private const UNKNOWN_COUNTRIES = ['XX', 'T1', 'ZZ', 'A1', 'A2'];

    /**
     * @return array{ip_address: ?string, country: ?string, user_agent: ?string}
     */
    public function capture(?Request $request = null): array
    {
        return $this->captureVisit($request);
    }

    /**
     * @return array{ip_address: ?string, country: ?string, user_agent: ?string}
     */
    public function captureVisit(?Request $request = null): array
    {
        $request ??= request();
        $ipAddress = $this->ipAddress($request);
        $userAgent = trim((string) $request->userAgent());

        return [
            'ip_address' => $ipAddress,
            'country' => $this->countryCode($request, $ipAddress),
            'user_agent' => $userAgent !== '' ? mb_substr($userAgent, 0, 191) : null,
        ];
    }

    public function captureCountryFromHeaders(?Request $request = null): ?string
    {
        $request ??= request();

        foreach (self::COUNTRY_HEADERS as $header) {
            $code = $this->normalizeCountry((string) $request->headers->get($header, ''));

            if ($code !== null) {
                return $code;
            }
        }

        return null;
    }

    private function ipAddress(Request $request): ?string
    {
        foreach (self::IP_HEADERS as $header) {
            foreach (explode(',', (string) $request->headers->get($header, '')) as $candidate) {
                $ip = $this->validIp($candidate);

                if ($ip !== null) {
                    return $ip;
                }
            }
        }

        return $this->validIp((string) $request->ip());
    }

    private function countryCode(Request $request, ?string $ipAddress): ?string
    {
        return $this->captureCountryFromHeaders($request) ?? $this->lookupCountry($ipAddress);
    }

    private function lookupCountry(?string $ipAddress): ?string
    {
        if ($ipAddress === null || ! $this->isPublicIp($ipAddress)) {
            return null;
        }

        $cached = Cache::remember(
            'visitor-origin-country:'.$ipAddress,
            now()->addDay(),
            fn (): string => $this->fetchCountry($ipAddress) ?? '',
        );

        return $this->normalizeCountry($cached);
    }

    private function fetchCountry(string $ipAddress): ?string
    {
        try {
            $response = Http::timeout(1.5)
                ->connectTimeout(1)
                ->acceptJson()
                ->get('https://ipwho.is/'.rawurlencode($ipAddress), [
                    'fields' => 'success,country_code',
                ]);
        } catch (Throwable) {
            return null;
        }

        if (! $response->ok() || $response->json('success') !== true) {
            return null;
        }

        return $this->normalizeCountry((string) $response->json('country_code'));
    }

    private function validIp(string $value): ?string
    {
        $ip = trim($value);

        if ($ip === '' || filter_var($ip, FILTER_VALIDATE_IP) === false) {
            return null;
        }

        return mb_substr($ip, 0, 45);
    }

    private function isPublicIp(string $ip): bool
    {
        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false;
    }

    private function normalizeCountry(string $value): ?string
    {
        $code = strtoupper(trim($value));

        if ($code === '' || strlen($code) !== 2 || ! ctype_alpha($code)) {
            return null;
        }

        if (in_array($code, self::UNKNOWN_COUNTRIES, true)) {
            return null;
        }

        return $code;
    }
}
