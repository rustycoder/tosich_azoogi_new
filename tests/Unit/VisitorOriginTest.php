<?php

namespace Tests\Unit;

use App\Services\VisitorOriginService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class VisitorOriginTest extends TestCase
{
    public function test_it_reads_cloudflare_ip_and_country(): void
    {
        $request = Request::create('/', 'POST', server: [
            'REMOTE_ADDR' => '10.0.0.1',
            'HTTP_CF_CONNECTING_IP' => '203.0.113.10',
            'HTTP_CF_IPCOUNTRY' => 'AU',
            'HTTP_USER_AGENT' => 'Mozilla/5.0',
        ]);

        $origin = (new VisitorOriginService)->capture($request);

        $this->assertSame('203.0.113.10', $origin['ip_address']);
        $this->assertSame('AU', $origin['country']);
        $this->assertSame('Mozilla/5.0', $origin['user_agent']);
    }

    public function test_it_looks_up_country_for_public_ips(): void
    {
        Http::fake([
            'ipwho.is/*' => Http::response([
                'success' => true,
                'country_code' => 'US',
            ]),
        ]);

        $request = Request::create('/', 'POST', server: [
            'REMOTE_ADDR' => '8.8.8.8',
        ]);

        $origin = (new VisitorOriginService)->capture($request);

        $this->assertSame('8.8.8.8', $origin['ip_address']);
        $this->assertSame('US', $origin['country']);
        Http::assertSent(fn ($http) => str_contains((string) $http->url(), 'ipwho.is/8.8.8.8'));
    }

    public function test_it_skips_lookup_for_private_ips(): void
    {
        Http::fake();

        $request = Request::create('/', 'POST', server: [
            'REMOTE_ADDR' => '127.0.0.1',
        ]);

        $origin = (new VisitorOriginService)->capture($request);

        $this->assertSame('127.0.0.1', $origin['ip_address']);
        $this->assertNull($origin['country']);
        Http::assertNothingSent();
    }

    public function test_it_ignores_unknown_cloudflare_country_codes(): void
    {
        Http::fake();

        $request = Request::create('/', 'POST', server: [
            'REMOTE_ADDR' => '127.0.0.1',
            'HTTP_CF_IPCOUNTRY' => 'XX',
        ]);

        $origin = (new VisitorOriginService)->capture($request);

        $this->assertNull($origin['country']);
        Http::assertNothingSent();
    }

    public function test_it_reads_country_from_headers_without_looking_up_ip(): void
    {
        Http::fake();

        $request = Request::create('/', 'GET', server: [
            'REMOTE_ADDR' => '8.8.8.8',
            'HTTP_CF_IPCOUNTRY' => 'NP',
        ]);

        $service = new VisitorOriginService;

        $this->assertSame('NP', $service->captureCountryFromHeaders($request));
        Http::assertNothingSent();
    }

    public function test_visit_capture_looks_up_country_for_public_ips_without_headers(): void
    {
        Http::fake([
            'ipwho.is/*' => Http::response([
                'success' => true,
                'country_code' => 'AU',
            ]),
        ]);

        $request = Request::create('/', 'GET', server: [
            'REMOTE_ADDR' => '8.8.8.8',
            'HTTP_USER_AGENT' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36',
        ]);

        $origin = (new VisitorOriginService)->captureVisit($request);

        $this->assertSame('8.8.8.8', $origin['ip_address']);
        $this->assertSame('AU', $origin['country']);
        $this->assertSame('Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36', $origin['user_agent']);
        Http::assertSent(fn ($http) => str_contains((string) $http->url(), 'ipwho.is/8.8.8.8'));
    }

    public function test_it_reads_the_first_forwarded_ip(): void
    {
        Http::fake([
            'ipwho.is/*' => Http::response([
                'success' => true,
                'country_code' => 'NP',
            ]),
        ]);

        $request = Request::create('/', 'GET', server: [
            'REMOTE_ADDR' => '10.0.0.1',
            'HTTP_X_FORWARDED_FOR' => '27.34.22.10, 10.0.0.1',
        ]);

        $origin = (new VisitorOriginService)->captureVisit($request);

        $this->assertSame('27.34.22.10', $origin['ip_address']);
        $this->assertSame('NP', $origin['country']);
    }
}
