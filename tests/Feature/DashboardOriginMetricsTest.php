<?php

namespace Tests\Feature;

use App\Enums\ContentResource;
use App\Models\ContentPermission;
use App\Models\Enquiry;
use App\Models\ProductDatasheetExport;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardOriginMetricsTest extends TestCase
{
    use RefreshDatabase;

    private const CHROME_MAC = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';

    private const CHROME_MAC_NEWER = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/121.0.0.0 Safari/537.36';

    private const SAFARI_IPHONE = 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1';

    public function test_admin_sees_enquiry_and_datasheet_bar_metrics(): void
    {
        $admin = User::factory()->admin()->create();

        Enquiry::factory()->quote()->create([
            'country' => 'AU',
            'user_agent' => self::CHROME_MAC,
        ]);
        Enquiry::factory()->product()->create([
            'country' => 'AU',
            'user_agent' => self::CHROME_MAC_NEWER,
        ]);
        Enquiry::factory()->contact()->create([
            'country' => 'US',
            'user_agent' => self::SAFARI_IPHONE,
        ]);
        Enquiry::factory()->contact()->create([
            'country' => null,
            'user_agent' => null,
        ]);

        ProductDatasheetExport::factory()->create([
            'country' => 'AU',
            'user_agent' => self::CHROME_MAC,
        ]);
        ProductDatasheetExport::factory()->create([
            'country' => 'GB',
            'user_agent' => self::SAFARI_IPHONE,
        ]);

        $this->actingAs($admin)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('>Overview</h1>', false)
            ->assertSee('dash-metrics', false)
            ->assertSee('data-metric', false)
            ->assertSee('dash-metric-bars', false)
            ->assertSee('dash-metric-bar-fill', false)
            ->assertSee('data-metric-tab="country"', false)
            ->assertSee('data-metric-tab="device"', false)
            ->assertSee('data-metric-panel="country"', false)
            ->assertSee('data-metric-panel="device"', false)
            ->assertSeeInOrder(['>Enquiries Metrics</h2>', '>Datasheet Metrics</h2>'], false)
            ->assertDontSee('dash-metric-total', false)
            ->assertDontSee('4 total', false)
            ->assertDontSee('2 total', false)
            ->assertSee('Australia', false)
            ->assertSee('United States', false)
            ->assertSee('United Kingdom', false)
            ->assertSee('Unknown', false)
            ->assertSee('50%', false)
            ->assertSee('25%', false)
            ->assertSee('Chrome on macOS', false)
            ->assertSee('Safari on iPhone', false);
    }

    public function test_enquiry_metrics_show_only_the_top_ten_countries(): void
    {
        $admin = User::factory()->admin()->create();
        $codes = ['AU', 'US', 'GB', 'NZ', 'CA', 'IN', 'DE', 'FR', 'JP', 'SG', 'BR'];

        foreach ($codes as $index => $code) {
            Enquiry::factory()->contact()->count(count($codes) - $index)->create([
                'name' => 'Metric Person '.$code,
                'company' => 'Metric Co '.$code,
                'message' => 'Metric note '.$code,
                'country' => $code,
                'user_agent' => self::CHROME_MAC,
            ]);
        }

        $this->actingAs($admin)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('title="Australia"', false)
            ->assertSee('title="Singapore"', false)
            ->assertDontSee('title="Brazil"', false);
    }

    public function test_enquiry_metrics_only_include_types_the_staff_can_manage(): void
    {
        $staff = User::factory()->staff()->create();
        ContentPermission::query()->create([
            'user_id' => $staff->id,
            'resource' => ContentResource::QuoteEnquiries,
        ]);

        Enquiry::factory()->quote()->create([
            'country' => 'AU',
            'user_agent' => self::CHROME_MAC,
        ]);
        Enquiry::factory()->product()->create([
            'country' => 'US',
            'user_agent' => self::SAFARI_IPHONE,
        ]);
        ProductDatasheetExport::factory()->create([
            'country' => 'GB',
            'user_agent' => self::CHROME_MAC,
        ]);

        $this->actingAs($staff)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('>Enquiries Metrics</h2>', false)
            ->assertSee('Australia', false)
            ->assertSee('100%', false)
            ->assertSee('Chrome on macOS', false)
            ->assertDontSee('>Datasheet Metrics</h2>', false)
            ->assertDontSee('United States', false)
            ->assertDontSee('United Kingdom', false)
            ->assertDontSee('Safari on iPhone', false);
    }

    public function test_datasheet_staff_see_datasheet_metrics_without_the_placeholder(): void
    {
        $staff = User::factory()->staff()->create();
        ContentPermission::query()->create([
            'user_id' => $staff->id,
            'resource' => ContentResource::Datasheet,
        ]);

        ProductDatasheetExport::factory()->create([
            'country' => 'AU',
            'user_agent' => self::CHROME_MAC,
        ]);

        $this->actingAs($staff)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('>Datasheet Metrics</h2>', false)
            ->assertSee('Australia', false)
            ->assertSee('100%', false)
            ->assertSee('Chrome on macOS', false)
            ->assertDontSee('>Enquiries Metrics</h2>', false)
            ->assertDontSee('Content tools for this account will be planned later', false);
    }

    public function test_customers_do_not_see_origin_metrics(): void
    {
        $customer = User::factory()->customer()->create();

        Enquiry::factory()->contact()->create([
            'country' => 'AU',
            'user_agent' => self::CHROME_MAC,
        ]);

        $this->actingAs($customer)
            ->get('/dashboard')
            ->assertOk()
            ->assertDontSee('>Enquiries Metrics</h2>', false)
            ->assertDontSee('>Datasheet Metrics</h2>', false)
            ->assertDontSee('dash-metrics', false)
            ->assertDontSee('>Audience Engagement Metrics</h2>', false)
            ->assertSee('Content tools for this account will be planned later', false);
    }

    public function test_admin_sees_yearly_engagement_column_chart(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-07 12:00:00', 'Australia/Sydney'));

        $admin = User::factory()->admin()->create();

        Enquiry::factory()->quote()->count(2)->create([
            'created_at' => '2026-01-15 10:00:00',
            'updated_at' => '2026-01-15 10:00:00',
        ]);
        Enquiry::factory()->quote()->create([
            'created_at' => '2025-06-01 10:00:00',
            'updated_at' => '2025-06-01 10:00:00',
        ]);
        ProductDatasheetExport::factory()->create([
            'created_at' => '2026-03-10 10:00:00',
            'updated_at' => '2026-03-10 10:00:00',
        ]);

        $this->actingAs($admin)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('>Audience Engagement Metrics</h2>', false)
            ->assertSee('dash-metric-year">2026</span>', false)
            ->assertSee('dash-metric-chart', false)
            ->assertSee('dash-metric-col is-enquiries', false)
            ->assertSee('dash-metric-col is-datasheets', false)
            ->assertSee('data-series="enquiries"', false)
            ->assertSee('data-series="datasheets"', false)
            ->assertSee('data-enquiries="2,0,0,0,0,0,0,0,0,0,0,0"', false)
            ->assertSee('data-datasheets="0,0,1,0,0,0,0,0,0,0,0,0"', false)
            ->assertSee('dash-metric-grid', false)
            ->assertSee('dash-metric-axis is-y', false)
            ->assertSee('>2</text>', false)
            ->assertSee('>1</text>', false)
            ->assertSee('>0</text>', false)
            ->assertDontSee('dash-metric-area', false)
            ->assertDontSee('dash-metric-badge', false)
            ->assertSee('>Jan</text>', false)
            ->assertSee('>Dec</text>', false);

        Carbon::setTestNow();
    }

    public function test_engagement_chart_only_includes_series_the_staff_can_manage(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-07 12:00:00', 'Australia/Sydney'));

        $staff = User::factory()->staff()->create();
        ContentPermission::query()->create([
            'user_id' => $staff->id,
            'resource' => ContentResource::QuoteEnquiries,
        ]);

        Enquiry::factory()->quote()->create([
            'created_at' => '2026-02-02 10:00:00',
            'updated_at' => '2026-02-02 10:00:00',
        ]);
        Enquiry::factory()->product()->create([
            'created_at' => '2026-02-02 10:00:00',
            'updated_at' => '2026-02-02 10:00:00',
        ]);
        ProductDatasheetExport::factory()->create([
            'created_at' => '2026-02-02 10:00:00',
            'updated_at' => '2026-02-02 10:00:00',
        ]);

        $this->actingAs($staff)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('>Audience Engagement Metrics</h2>', false)
            ->assertSee('dash-metric-col is-enquiries', false)
            ->assertSee('data-series="enquiries"', false)
            ->assertSee('data-enquiries="0,1,0,0,0,0,0,0,0,0,0,0"', false)
            ->assertDontSee('dash-metric-col is-datasheets', false)
            ->assertDontSee('data-series="datasheets"', false)
            ->assertDontSee('data-datasheets=', false)
            ->assertDontSee('dash-metric-swatch is-datasheets', false);

        Carbon::setTestNow();
    }
}
