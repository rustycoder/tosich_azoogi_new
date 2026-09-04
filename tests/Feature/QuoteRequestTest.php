<?php

namespace Tests\Feature;

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
            ->assertSee('Get a Custom Quote', false);
    }
}
