<?php

namespace Tests\Feature;

use App\Enums\Status;
use App\Models\Page;
use App\Models\PageMeta;
use App\Models\User;
use Database\Seeders\AdminUserSeeder;
use Database\Seeders\PageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuoteRequestTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PageSeeder::class);
    }

    public function test_header_includes_quote_drawer_beside_led_calculator(): void
    {
        $this->get('/products')
            ->assertOk()
            ->assertSee('id="quote-trigger"', false)
            ->assertSee('aria-label="Quote list"', false)
            ->assertSee('id="quote-drawer"', false)
            ->assertSee('Quote List', false)
            ->assertSee('Request a Quote', false)
            ->assertSee(url('/request-a-quote'), false)
            ->assertSee(url('/led-strip-calculator'), false)
            ->assertSeeInOrder([
                'class="nav-actions"',
                'LED Calculator',
                'id="quote-trigger"',
            ], false)
            ->assertSee('LED Calculator', false)
            ->assertSee('assets/css/quote.css', false)
            ->assertSee('assets/js/quote.js', false);
    }

    public function test_quote_request_page_has_product_list_and_form(): void
    {
        $this->get('/request-a-quote')
            ->assertOk()
            ->assertSee('Request a Quote', false)
            ->assertSee('Products in this quote', false)
            ->assertSee('data-quote-list="page"', false)
            ->assertSee('id="quote-request-form"', false)
            ->assertSee('Get A Quote For Your Project', false)
            ->assertSee('Request details', false)
            ->assertSee('First Name*', false)
            ->assertSee('Products Needed + Quantities', false)
            ->assertSee('Which describes you best', false)
            ->assertSee('Preferred contact method', false)
            ->assertSee('Suburb or Retailer', false)
            ->assertSee('Get a Custom Quote', false)
            ->assertSee('action="'.route('quote.submit').'"', false);
    }

    public function test_quote_form_validates_required_fields(): void
    {
        $this->from('/request-a-quote')
            ->post('/request-a-quote', [])
            ->assertRedirect('/request-a-quote')
            ->assertSessionHasErrors(['your-name', 'your-email', 'your-phone']);
    }

    public function test_quote_form_accepts_a_valid_submission(): void
    {
        $this->from('/request-a-quote')
            ->post('/request-a-quote', [
                'your-name' => 'Jane Example',
                'your-email' => 'jane@example.com',
                'your-phone' => '0400 000 000',
                'your-description' => 'Hotel lobby',
                'your-products' => '2x Neon Flex',
                'radio-choice' => 'I’m an Architect',
                'contact-choice' => 'Email',
                'suburb-retailer' => 'Matraville',
            ])
            ->assertRedirect('/request-a-quote')
            ->assertSessionHas('status');
    }

    public function test_quote_page_accepts_font_size_and_alignment(): void
    {
        $this->seed(AdminUserSeeder::class);
        $admin = User::query()->where('email', 'admin@azoogi.com')->firstOrFail();
        $page = Page::query()->where('slug', 'request-a-quote')->firstOrFail();
        $meta = PageMeta::query()->where('page_id', $page->id)->where('key', 'intro.title')->firstOrFail();

        $this->actingAs($admin)
            ->put(route('dashboard.pages.update', $page), [
                'title' => $page->title,
                'meta_description' => $page->meta_description,
                'status' => Status::Active->value,
                'meta' => [
                    $meta->id => [
                        'value' => $meta->value,
                        'font_size' => '32px',
                        'text_align' => 'center',
                    ],
                ],
            ])
            ->assertRedirect();

        $this->get('/request-a-quote')
            ->assertOk()
            ->assertSee('style="font-size: 32px; text-align: center"', false);
    }
}
