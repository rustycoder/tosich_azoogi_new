<?php

namespace Tests\Feature;

use App\Enums\PageVisitKind;
use App\Enums\Status;
use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PageVisitTest extends TestCase
{
    use RefreshDatabase;

    public function test_cms_get_records_a_page_visit_from_country_headers_only(): void
    {
        Http::fake();

        $this->withHeaders([
            'CF-IPCountry' => 'NP',
            'CF-Connecting-IP' => '2400:1a00:4b28:aef4:8038:fc7a:95ce:9fd3',
            'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        ])
            ->get('/about')
            ->assertOk();

        $this->assertDatabaseHas('page_visits', [
            'kind' => PageVisitKind::Page->value,
            'page_slug' => 'about',
            'airtable_id' => null,
            'country' => 'NP',
            'ip_address' => '2400:1a00:4b28:aef4:8038:fc7a:95ce:9fd3',
            'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        ]);
        Http::assertNothingSent();
    }

    public function test_cms_get_looks_up_country_when_headers_are_missing(): void
    {
        Http::fake([
            'ipwho.is/*' => Http::response([
                'success' => true,
                'country_code' => 'AU',
            ]),
        ]);

        $this->withServerVariables(['REMOTE_ADDR' => '8.8.8.8'])
            ->get('/about')
            ->assertOk();

        $this->assertDatabaseHas('page_visits', [
            'kind' => PageVisitKind::Page->value,
            'page_slug' => 'about',
            'country' => 'AU',
            'ip_address' => '8.8.8.8',
        ]);
    }

    public function test_home_records_the_home_slug(): void
    {
        $this->get('/')->assertOk();

        $this->assertDatabaseHas('page_visits', [
            'kind' => PageVisitKind::Page->value,
            'page_slug' => 'home',
        ]);
    }

    public function test_product_detail_with_id_records_a_product_visit(): void
    {
        Http::fake();

        $this->withHeaders([
            'CF-IPCountry' => 'AU',
            'CF-Connecting-IP' => '203.0.113.10',
            'User-Agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1',
        ])
            ->get('/product-detail?id=recGardenLight01')
            ->assertOk();

        $this->assertDatabaseHas('page_visits', [
            'kind' => PageVisitKind::Product->value,
            'page_slug' => null,
            'airtable_id' => 'recGardenLight01',
            'country' => 'AU',
            'ip_address' => '203.0.113.10',
            'user_agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1',
        ]);
        Http::assertNothingSent();
    }

    public function test_product_detail_without_id_is_not_recorded(): void
    {
        $this->get('/product-detail')->assertOk();

        $this->assertDatabaseCount('page_visits', 0);
    }

    public function test_catalogue_and_trade_login_are_not_recorded(): void
    {
        $this->get('/products')->assertOk();
        $this->get('/trade-login')->assertOk();

        $this->assertDatabaseCount('page_visits', 0);
    }

    public function test_staff_and_admin_visits_are_not_recorded(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->get('/about')
            ->assertOk();

        $this->actingAs(User::factory()->staff()->create())
            ->get('/contact')
            ->assertOk();

        $this->assertDatabaseCount('page_visits', 0);
    }

    public function test_customer_visits_are_recorded(): void
    {
        $this->actingAs(User::factory()->customer()->create())
            ->get('/about')
            ->assertOk();

        $this->assertDatabaseCount('page_visits', 1);
    }

    public function test_bot_user_agents_are_not_recorded(): void
    {
        $this->withHeaders(['User-Agent' => 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)'])
            ->get('/about')
            ->assertOk();

        $this->assertDatabaseCount('page_visits', 0);
    }

    public function test_missing_pages_are_not_recorded(): void
    {
        Page::query()->where('slug', 'about')->update(['status' => Status::Inactive]);

        $this->get('/about')->assertNotFound();

        $this->assertDatabaseCount('page_visits', 0);
    }
}
