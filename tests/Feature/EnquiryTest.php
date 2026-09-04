<?php

namespace Tests\Feature;

use App\Enums\ContentResource;
use App\Enums\EnquiryStatus;
use App\Enums\EnquiryType;
use App\Models\ContentPermission;
use App\Models\Enquiry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnquiryTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_enquiry_requires_core_fields(): void
    {
        $this->from('/product-detail')
            ->post('/product-enquiry', [])
            ->assertRedirect('/product-detail')
            ->assertSessionHasErrors(['quote-name', 'quote-email', 'quote-company', 'quote-project', 'quote-spec']);
    }

    public function test_product_enquiry_is_stored_as_pending(): void
    {
        $this->from('/product-detail')
            ->post('/product-enquiry', [
                'quote-name' => 'Pat Buyer',
                'quote-email' => 'pat@example.com',
                'quote-company' => 'Summit Electrical',
                'quote-project' => 'Harbour pavilion',
                'quote-spec' => "Product: Garden Light (Garden Light)\nVariant Model: GL005",
                'quote-message' => 'Need a black finish.',
            ])
            ->assertRedirect('/product-detail')
            ->assertSessionHas('status');

        $this->assertDatabaseHas('enquiries', [
            'type' => EnquiryType::Product->value,
            'status' => EnquiryStatus::Pending->value,
            'name' => 'Pat Buyer',
            'email' => 'pat@example.com',
            'company' => 'Summit Electrical',
        ]);
    }

    public function test_admin_sees_enquiries_kanban_and_pending_counts(): void
    {
        $admin = User::factory()->admin()->create();
        Enquiry::factory()->quote()->pending()->create(['name' => 'Quote Person']);
        Enquiry::factory()->product()->pending()->create(['name' => 'Product Person']);
        Enquiry::factory()->contact()->done()->create(['name' => 'Done Contact']);

        $this->actingAs($admin)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('data-enquiry-kanban', false)
            ->assertSee('data-pending-only', false)
            ->assertSee('data-enquiry-move', false)
            ->assertSee('Move to', false)
            ->assertDontSee('dash-drag-handle', false)
            ->assertSee('Quote Person', false)
            ->assertSee('Product Person', false)
            ->assertDontSee('Done Contact', false)
            ->assertSeeInOrder(['>Projects</h2>', '>Quote Enquiries</h2>', '>Product Enquiries</h2>', '>Contact Enquiries</h2>'], false)
            ->assertSee('aria-label="View all"', false)
            ->assertDontSee('>View all</a>', false)
            ->assertSeeInOrder(['>Enquiries</div>', 'Quote', 'Product', 'Contact'], false);

        $this->actingAs($admin)
            ->get('/dashboard/enquiries')
            ->assertOk()
            ->assertSee('Quote Person', false);

        $this->actingAs($admin)
            ->get('/dashboard/enquiries/quote')
            ->assertOk()
            ->assertSee('data-enquiry-kanban', false)
            ->assertSee('dash-drag-handle', false)
            ->assertSee('data-enquiry-dialog', false)
            ->assertSee('data-enquiry-move', false)
            ->assertSee('data-enquiry-open', false)
            ->assertDontSee('dash-kanban-preview', false)
            ->assertSeeInOrder(['Pending', 'Active', 'Done', 'Cancelled'], false)
            ->assertSee('Quote Person', false)
            ->assertDontSee('Product Person', false)
            ->assertDontSee('Done Contact', false);

        $this->actingAs($admin)
            ->get('/dashboard/enquiries/products')
            ->assertOk()
            ->assertSee('Product Person', false)
            ->assertDontSee('Quote Person', false);

        $this->actingAs($admin)
            ->get('/dashboard/enquiries/contacts')
            ->assertOk()
            ->assertSee('Done Contact', false)
            ->assertDontSee('Quote Person', false);
    }

    public function test_staff_need_enquiries_permission(): void
    {
        $staff = User::factory()->staff()->create();

        $this->actingAs($staff)->get('/dashboard/enquiries/quote')->assertForbidden();

        ContentPermission::query()->create([
            'user_id' => $staff->id,
            'resource' => ContentResource::QuoteEnquiries,
        ]);

        $this->actingAs($staff->fresh())->get('/dashboard/enquiries/quote')->assertOk();
        $this->actingAs($staff->fresh())->get('/dashboard/enquiries/products')->assertForbidden();
        $this->actingAs($staff->fresh())->get('/dashboard/enquiries/contacts')->assertForbidden();
        $this->actingAs($staff->fresh())
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Quote Enquiries', false)
            ->assertDontSee('Product Enquiries', false)
            ->assertDontSee('Contact Enquiries', false);
    }

    public function test_enquiry_status_can_be_updated(): void
    {
        $admin = User::factory()->admin()->create();
        $enquiry = Enquiry::factory()->contact()->pending()->create();

        $this->actingAs($admin)
            ->patchJson(route('dashboard.enquiries.status', $enquiry), [
                'status' => EnquiryStatus::Active->value,
            ])
            ->assertOk()
            ->assertJsonPath('status', EnquiryStatus::Active->value)
            ->assertJsonPath('updated_by', $admin->name)
            ->assertJsonPath('updated_at', $enquiry->fresh()->updated_at?->timezone(config('app.timezone'))->format('j M Y, g:i A'));

        $this->assertSame(EnquiryStatus::Active, $enquiry->fresh()->status);
    }

    public function test_enquiry_card_keeps_details_for_the_dialog(): void
    {
        $admin = User::factory()->admin()->create(['name' => 'Pat Admin']);
        $this->actingAs($admin);
        Enquiry::factory()->contact()->pending()->create([
            'name' => 'Pratik Man Joshi',
            'email' => 'pratik.man.joshi@gmail.com',
            'message' => 'Need a site visit next week.',
        ]);

        $this->get('/dashboard/enquiries/contacts')
            ->assertOk()
            ->assertSee('Pratik Man Joshi', false)
            ->assertSee('pratik.man.joshi@gmail.com', false)
            ->assertSee('Need a site visit next week.', false)
            ->assertSee('mailto:pratik.man.joshi@gmail.com', false)
            ->assertSee('dash-enquiry-facts', false)
            ->assertSee('dash-enquiry-note', false)
            ->assertSee('Pat Admin', false)
            ->assertSee('data-updater-name', false)
            ->assertSee('data-updated-at', false)
            ->assertDontSee('Last updated', false)
            ->assertSee('data-enquiry-delete', false)
            ->assertSee('data-enquiry-move', false)
            ->assertSee('Move to', false)
            ->assertSee('data-status-labels', false)
            ->assertSee('data-enquiry-status', false)
            ->assertDontSee('>Name</dt>', false)
            ->assertDontSee('dash-kanban-preview', false);
    }

    public function test_enquiry_can_be_deleted(): void
    {
        $admin = User::factory()->admin()->create();
        $enquiry = Enquiry::factory()->contact()->pending()->create();

        $this->actingAs($admin)
            ->deleteJson(route('dashboard.enquiries.destroy', $enquiry))
            ->assertOk()
            ->assertJsonPath('message', 'Enquiry deleted.');

        $this->assertSoftDeleted($enquiry);
        $this->assertSame($admin->id, $enquiry->fresh()->deleted_by);
    }

    public function test_staff_without_enquiries_permission_cannot_delete(): void
    {
        $staff = User::factory()->staff()->create();
        $enquiry = Enquiry::factory()->contact()->pending()->create();

        $this->actingAs($staff)
            ->deleteJson(route('dashboard.enquiries.destroy', $enquiry))
            ->assertForbidden();

        $this->assertNotSoftDeleted($enquiry);
    }
}
