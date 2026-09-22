<?php

namespace Tests\Feature;

use App\Services\Contracts\ITurnstileService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TurnstileValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_form_fails_validation_when_turnstile_is_enabled_and_response_missing(): void
    {
        config(['services.turnstile.enabled' => true]);

        $this->from('/contact')
            ->post('/contact', [
                'your-name' => 'John Doe',
                'your-email' => 'john@example.com',
                'your-company' => 'Acme Inc',
                'your-message' => 'Hello there',
            ])
            ->assertRedirect('/contact')
            ->assertSessionHasErrors(['cf-turnstile-response']);
    }

    public function test_contact_form_passes_validation_when_turnstile_is_verified(): void
    {
        config([
            'services.turnstile.enabled' => true,
            'services.turnstile.secret_key' => 'dummy-secret',
        ]);

        Http::fake([
            'https://challenges.cloudflare.com/turnstile/v0/siteverify' => Http::response([
                'success' => true,
                'challenge_ts' => now()->toISOString(),
                'hostname' => 'example.com',
                'error-codes' => [],
            ], 200),
        ]);

        $this->from('/contact')
            ->post('/contact', [
                'your-name' => 'John Doe',
                'your-email' => 'john@example.com',
                'your-company' => 'Acme Inc',
                'your-message' => 'Hello there',
                'cf-turnstile-response' => 'valid-mock-token',
            ])
            ->assertRedirect('/contact')
            ->assertSessionHas('status');

        $this->assertDatabaseHas('enquiries', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'company' => 'Acme Inc',
        ]);
    }

    public function test_contact_form_fails_when_turnstile_api_rejects_token(): void
    {
        config([
            'services.turnstile.enabled' => true,
            'services.turnstile.secret_key' => 'dummy-secret',
        ]);

        Http::fake([
            'https://challenges.cloudflare.com/turnstile/v0/siteverify' => Http::response([
                'success' => false,
                'error-codes' => ['invalid-input-response'],
            ], 200),
        ]);

        $this->from('/contact')
            ->post('/contact', [
                'your-name' => 'John Doe',
                'your-email' => 'john@example.com',
                'your-company' => 'Acme Inc',
                'your-message' => 'Hello there',
                'cf-turnstile-response' => 'invalid-token',
            ])
            ->assertRedirect('/contact')
            ->assertSessionHasErrors(['cf-turnstile-response']);
    }

    public function test_product_enquiry_requires_turnstile_when_enabled(): void
    {
        config(['services.turnstile.enabled' => true]);

        $this->from('/product-detail')
            ->post('/product-enquiry', [
                'quote-name' => 'Jane Smith',
                'quote-email' => 'jane@example.com',
                'quote-company' => 'Design Studio',
                'quote-project' => 'Hotel Alpha',
                'quote-spec' => 'Model X',
                'quote-message' => 'Need 10 units',
            ])
            ->assertRedirect('/product-detail')
            ->assertSessionHasErrors(['cf-turnstile-response']);
    }

    public function test_datasheet_export_requires_turnstile_when_enabled(): void
    {
        config(['services.turnstile.enabled' => true]);

        $this->postJson('/product-datasheet', [
            'product_id' => 'prod-123',
            'project_name' => 'Delta Tower',
            'person_name' => 'Sam Taylor',
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['cf-turnstile-response']);
    }

    public function test_turnstile_service_returns_false_on_network_error(): void
    {
        config([
            'services.turnstile.enabled' => true,
            'services.turnstile.secret_key' => 'dummy-secret',
        ]);

        Http::fake([
            'https://challenges.cloudflare.com/turnstile/v0/siteverify' => Http::response(null, 500),
        ]);

        $service = app(ITurnstileService::class);
        $result = $service->verify('some-token', '127.0.0.1');

        $this->assertFalse($result);
    }
}
