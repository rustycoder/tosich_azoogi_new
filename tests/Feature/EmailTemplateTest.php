<?php

namespace Tests\Feature;

use App\Enums\UserType;
use App\Mail\DynamicNotificationMail;
use App\Models\Product;
use App\Models\User;
use App\Services\EmailTemplateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class EmailTemplateTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $staff;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'user_type' => UserType::Admin,
        ]);

        $this->staff = User::factory()->create([
            'user_type' => UserType::Staff,
        ]);
    }

    public function test_admin_can_view_email_templates_index(): void
    {
        $response = $this->actingAs($this->admin)->get(route('dashboard.email-templates.index'));

        $response->assertOk()
            ->assertSee('Email Notifications & Templates', false)
            ->assertSee('Contact Enquiry Notification')
            ->assertSee('Product Enquiry Notification')
            ->assertSee('Quote Request Notification')
            ->assertSee('Custom Datasheet Download Notification');
    }

    public function test_staff_cannot_view_email_templates_index(): void
    {
        $response = $this->actingAs($this->staff)->get(route('dashboard.email-templates.index'));

        $response->assertForbidden();
    }

    public function test_admin_can_view_edit_template_page_with_tokens(): void
    {
        /** @var EmailTemplateService $service */
        $service = app(EmailTemplateService::class);
        $template = $service->getOrCreate(EmailTemplateService::KEY_CONTACT);

        $response = $this->actingAs($this->admin)->get(route('dashboard.email-templates.edit', $template));

        $response->assertOk()
            ->assertSee('+ {{name}}', false)
            ->assertSee('+ {{email}}', false)
            ->assertSee('+ {{company}}', false)
            ->assertSee('+ {{message}}', false)
            ->assertDontSee('+ {{{{ $token }}}}', false);
    }

    public function test_admin_can_update_email_template(): void
    {
        /** @var EmailTemplateService $service */
        $service = app(EmailTemplateService::class);
        $template = $service->getOrCreate(EmailTemplateService::KEY_CONTACT);

        $response = $this->actingAs($this->admin)->put(route('dashboard.email-templates.update', $template), [
            'recipient_emails' => 'custom-adrian@azoogi.com, support@azoogi.com',
            'subject' => 'Custom Subject: {{name}}',
            'body_html' => '<p>Custom email body for {{name}}</p>',
        ]);

        $response->assertRedirect(route('dashboard.email-templates.edit', $template))
            ->assertSessionHas('status');

        $template->refresh();
        $this->assertSame('custom-adrian@azoogi.com, support@azoogi.com', $template->recipient_emails);
        $this->assertSame('Custom Subject: {{name}}', $template->subject);
        $this->assertSame('<p>Custom email body for {{name}}</p>', $template->body_html);
        $this->assertEquals(['custom-adrian@azoogi.com', 'support@azoogi.com'], $template->recipientList());
    }

    public function test_admin_can_preview_template_ajax(): void
    {
        /** @var EmailTemplateService $service */
        $service = app(EmailTemplateService::class);
        $template = $service->getOrCreate(EmailTemplateService::KEY_CONTACT);

        $response = $this->actingAs($this->admin)->postJson(route('dashboard.email-templates.preview', $template), [
            'subject' => 'Testing subject with {{name}}',
            'body_html' => '<div>Hello {{name}} from {{company}}</div>',
        ]);

        $response->assertOk()
            ->assertJson([
                'subject' => 'Testing subject with Adrian Vance',
                'body_html' => '<div>Hello Adrian Vance from Vance Architectural Studio</div>',
            ]);
    }

    public function test_admin_can_send_test_email(): void
    {
        Mail::fake();

        /** @var EmailTemplateService $service */
        $service = app(EmailTemplateService::class);
        $template = $service->getOrCreate(EmailTemplateService::KEY_CONTACT);

        $response = $this->actingAs($this->admin)->postJson(route('dashboard.email-templates.test', $template), [
            'test_email' => 'developer@azoogi.com',
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
            ]);

        Mail::assertSent(DynamicNotificationMail::class, function (DynamicNotificationMail $mail) {
            return $mail->hasTo('developer@azoogi.com') && str_contains($mail->renderedSubject, '[TEST]');
        });
    }

    public function test_admin_can_reset_template_to_default(): void
    {
        /** @var EmailTemplateService $service */
        $service = app(EmailTemplateService::class);
        $template = $service->getOrCreate(EmailTemplateService::KEY_CONTACT);

        $template->update([
            'subject' => 'Altered Subject',
            'body_html' => '<p>Altered Body</p>',
        ]);

        $response = $this->actingAs($this->admin)->post(route('dashboard.email-templates.reset', $template));

        $response->assertRedirect(route('dashboard.email-templates.edit', $template));

        $template->refresh();
        $this->assertSame('New Contact Enquiry from {{name}}', $template->subject);
        $this->assertStringContainsString('New Contact Enquiry', $template->body_html);
    }

    public function test_admin_can_toggle_template_status(): void
    {
        /** @var EmailTemplateService $service */
        $service = app(EmailTemplateService::class);
        $template = $service->getOrCreate(EmailTemplateService::KEY_CONTACT);

        $response = $this->actingAs($this->admin)->patchJson(route('dashboard.email-templates.toggle-status', $template));

        $response->assertOk()
            ->assertJson([
                'on' => false,
                'label' => 'Disabled',
            ]);

        $this->assertFalse($template->fresh()->is_active);
    }

    public function test_submitting_contact_enquiry_sends_email_to_adrian(): void
    {
        Mail::fake();

        $response = $this->post('/contact', [
            'your-name' => 'Sarah Connor',
            'your-email' => 'sarah@example.com',
            'your-company' => 'Cyberdyne',
            'your-message' => 'Need lighting consultation.',
        ]);

        $response->assertRedirect();

        Mail::assertSent(DynamicNotificationMail::class, function (DynamicNotificationMail $mail) {
            return $mail->hasTo('adrian@azoogi.com')
                && str_contains($mail->renderedSubject, 'Sarah Connor')
                && str_contains($mail->renderedHtml, 'Cyberdyne')
                && $mail->hasReplyTo('sarah@example.com');
        });
    }

    public function test_submitting_product_enquiry_sends_email_to_sales(): void
    {
        Mail::fake();

        $response = $this->post('/product-enquiry', [
            'quote-name' => 'John Architect',
            'quote-email' => 'john@example.com',
            'quote-company' => 'Studio Design',
            'quote-project' => 'Opera House',
            'quote-spec' => 'Model: Slim 7 3000K',
            'quote-message' => 'Need 50 units.',
        ]);

        $response->assertRedirect();

        Mail::assertSent(DynamicNotificationMail::class, function (DynamicNotificationMail $mail) {
            return $mail->hasTo('sales@azoogi.com')
                && str_contains($mail->renderedSubject, 'John Architect')
                && str_contains($mail->renderedSubject, 'Opera House')
                && str_contains($mail->renderedHtml, 'Studio Design')
                && $mail->hasReplyTo('john@example.com');
        });
    }

    public function test_submitting_quote_request_sends_email_to_sales(): void
    {
        Mail::fake();

        $response = $this->post('/request-a-quote', [
            'your-name' => 'Michael Builder',
            'your-email' => 'michael@example.com',
            'your-phone' => '0400000000',
            'radio-choice' => 'Builder',
            'contact-choice' => 'Phone',
            'suburb-retailer' => 'Surry Hills',
            'your-description' => 'Commercial fitout package.',
            'your-products' => json_encode([
                ['name' => 'Pro LED Strip', 'code' => 'PLS-100', 'qty' => 5],
            ]),
        ]);

        $response->assertRedirect();

        Mail::assertSent(DynamicNotificationMail::class, function (DynamicNotificationMail $mail) {
            return $mail->hasTo('sales@azoogi.com')
                && str_contains($mail->renderedSubject, 'Michael Builder')
                && str_contains($mail->renderedHtml, 'Pro LED Strip')
                && str_contains($mail->renderedHtml, 'Surry Hills')
                && $mail->hasReplyTo('michael@example.com');
        });
    }

    public function test_downloading_custom_datasheet_sends_email_to_sales(): void
    {
        Mail::fake();

        $product = Product::factory()->create([
            'airtable_id' => 'rec123456789',
            'product_name' => 'High Output COB Strip',
            'product_code' => 'COB-100',
        ]);

        $response = $this->postJson('/product-datasheet', [
            'product_id' => 'rec123456789',
            'person_name' => 'Alex Designer',
            'project_name' => 'Crown Hotel Suite',
            'selected_options' => [
                'CCT' => '4000K',
                'IP' => 'IP67',
            ],
        ]);

        $response->assertOk();

        Mail::assertSent(DynamicNotificationMail::class, function (DynamicNotificationMail $mail) {
            return $mail->hasTo('sales@azoogi.com')
                && str_contains($mail->renderedSubject, 'High Output COB Strip')
                && str_contains($mail->renderedHtml, 'Alex Designer')
                && str_contains($mail->renderedHtml, 'Crown Hotel Suite');
        });
    }
}
