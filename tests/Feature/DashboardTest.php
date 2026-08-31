<?php

namespace Tests\Feature;

use App\Enums\ContentResource;
use App\Enums\Status;
use App\Models\ContentPermission;
use App\Models\Page;
use App\Models\PageMeta;
use App\Models\Project;
use App\Models\User;
use Database\Seeders\AdminUserSeeder;
use Database\Seeders\PageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $this->seed(PageSeeder::class);

        $this->get('/dashboard')->assertRedirect('/login');
        $this->get('/dashboard/settings')->assertRedirect('/login');
        $this->get('/dashboard/content/pages/home/preview')->assertRedirect('/login');
        $this->get('/dashboard/content/sections')->assertRedirect('/login');
    }

    public function test_admin_can_open_dashboard_staff_and_page_editors(): void
    {
        $this->seed([AdminUserSeeder::class, PageSeeder::class]);
        $admin = User::query()->where('email', 'admin@azoogi.com')->firstOrFail();

        $this->actingAs($admin)->get('/dashboard')->assertOk();
        $this->actingAs($admin)->get('/dashboard/staff')->assertOk();
        $this->actingAs($admin)->get('/dashboard/content/pages')->assertOk()->assertSee('Home', false);
        $this->actingAs($admin)->get('/dashboard/content/pages/home')->assertOk();
        $this->actingAs($admin)->get('/dashboard/content/pages/home-owner')->assertOk();
        $this->actingAs($admin)->get('/dashboard/content/sections')->assertOk()->assertSee('Header', false)->assertSee('Footer', false);
        $this->actingAs($admin)->get('/dashboard/content/sections/header')->assertOk()->assertSee('dash-card', false)->assertDontSee('dash-visual-frame', false);
        $this->actingAs($admin)->get('/dashboard/content/projects')->assertOk();
    }

    public function test_staff_project_and_profile_screens_use_full_width_cards(): void
    {
        $admin = User::factory()->admin()->create();
        $staff = User::factory()->staff()->create();
        $project = Project::factory()->create();

        $this->actingAs($admin)->get('/dashboard')->assertOk()->assertSee('data-dash-menu', false);
        $this->actingAs($admin)->get('/dashboard/staff')->assertOk()->assertSee('dash-table-wrap', false);
        $this->actingAs($admin)->get('/dashboard/staff/create')->assertOk()->assertSee('dash-card', false);
        $this->actingAs($admin)->get(route('dashboard.staff.edit', $staff))->assertOk()->assertSee('dash-card', false);
        $this->actingAs($admin)->get('/dashboard/content/projects')->assertOk()->assertSee('dash-table-wrap', false);
        $this->actingAs($admin)
            ->get('/dashboard/content/projects/create')
            ->assertOk()
            ->assertSee('dash-card', false)
            ->assertSee('>Details</h2>', false)
            ->assertSee('>Gallery</h2>', false)
            ->assertSee('name="gallery_files[]"', false);
        $this->actingAs($admin)
            ->get(route('dashboard.projects.edit', $project))
            ->assertOk()
            ->assertSee('dash-card', false)
            ->assertSee('>Details</h2>', false)
            ->assertSee('>Gallery</h2>', false);
        $this->actingAs($admin)->get('/dashboard/settings')->assertOk()->assertSee('dash-card', false);
    }

    public function test_pages_index_is_ordered_by_title_ascending(): void
    {
        $this->seed([AdminUserSeeder::class, PageSeeder::class]);
        $admin = User::query()->where('email', 'admin@azoogi.com')->firstOrFail();

        $html = $this->actingAs($admin)
            ->get('/dashboard/content/pages')
            ->assertOk()
            ->getContent();

        $about = strpos($html, '>About</a>');
        $home = strpos($html, '>Home</a>');
        $wholesaler = strpos($html, '>Wholesaler</a>');

        $this->assertNotFalse($about);
        $this->assertNotFalse($home);
        $this->assertNotFalse($wholesaler);
        $this->assertTrue($about < $home);
        $this->assertTrue($home < $wholesaler);
        $this->assertStringNotContainsString('>Header</a>', $html);
        $this->assertStringNotContainsString('>Footer</a>', $html);
    }

    public function test_index_tables_show_who_last_updated_and_when(): void
    {
        $admin = User::factory()->admin()->create(['name' => 'Pat Admin']);
        $this->actingAs($admin);

        $staff = User::factory()->staff()->create();
        $project = Project::factory()->create();
        $this->seed(PageSeeder::class);
        $page = Page::query()->where('slug', 'about')->firstOrFail();
        $page->update(['title' => $page->title]);

        $this->get('/dashboard/staff')
            ->assertOk()
            ->assertSee('Last updated', false)
            ->assertSee('Pat Admin', false)
            ->assertSee('dash-updated', false);

        $this->get('/dashboard/content/projects')
            ->assertOk()
            ->assertSee('Last updated', false)
            ->assertSee('Pat Admin', false)
            ->assertSee('dash-updated', false);

        $this->get('/dashboard/content/pages')
            ->assertOk()
            ->assertSee('Last updated', false)
            ->assertSee('Pat Admin', false)
            ->assertSee('dash-updated', false);

        $this->assertNotNull($staff->fresh()->updated_by);
        $this->assertNotNull($project->fresh()->updated_by);
        $this->assertSame($admin->id, $page->fresh()->updated_by);
    }

    public function test_staff_can_only_manage_assigned_resources(): void
    {
        $this->seed(PageSeeder::class);

        $staff = User::factory()->staff()->create();
        ContentPermission::query()->create([
            'user_id' => $staff->id,
            'resource' => ContentResource::About,
        ]);

        $this->actingAs($staff)->get('/dashboard')->assertOk()->assertSee('Pages', false)->assertDontSee('content/pages/home', false);
        $this->actingAs($staff)->get('/dashboard/content/pages')->assertOk()->assertSee('About', false)->assertDontSee('content/pages/home', false);
        $this->actingAs($staff)->get('/dashboard/content/pages/about')->assertOk();
        $this->actingAs($staff)->get('/dashboard/content/pages/home')->assertForbidden();
        $this->actingAs($staff)->get('/dashboard/content/sections')->assertForbidden();
        $this->actingAs($staff)->get('/dashboard/content/projects')->assertForbidden();
        $this->actingAs($staff)->get('/dashboard/staff')->assertForbidden();
    }

    public function test_customer_and_trader_see_placeholder_dashboard_only(): void
    {
        $this->seed(PageSeeder::class);

        foreach ([User::factory()->customer()->create(), User::factory()->trader()->create()] as $user) {
            $this->actingAs($user)
                ->get('/dashboard')
                ->assertOk()
                ->assertSee('Content tools for this account will be planned later', false);

            $this->actingAs($user)->get('/dashboard/content/pages')->assertForbidden();
            $this->actingAs($user)->get('/dashboard/content/pages/home')->assertForbidden();
            $this->actingAs($user)->get('/dashboard/content/sections')->assertForbidden();
            $this->actingAs($user)->get('/dashboard/staff')->assertForbidden();
        }
    }

    public function test_admin_can_update_page_content(): void
    {
        $this->seed([AdminUserSeeder::class, PageSeeder::class]);
        $admin = User::query()->where('email', 'admin@azoogi.com')->firstOrFail();
        $page = Page::query()->where('slug', 'about')->firstOrFail();
        $meta = PageMeta::query()->where('page_id', $page->id)->where('key', 'hero.kicker')->firstOrFail();

        $this->actingAs($admin)
            ->put(route('dashboard.pages.update', $page), [
                'title' => $page->title,
                'meta_description' => $page->meta_description,
                'status' => Status::Active->value,
                'meta' => [
                    $meta->id => ['value' => 'Updated Kicker'],
                ],
            ])
            ->assertRedirect();

        $this->assertSame('Updated Kicker', $meta->fresh()->value);
        $this->get('/about')->assertSee('Updated Kicker', false);
    }

    public function test_staff_can_create_and_soft_delete_projects(): void
    {
        $staff = User::factory()->staff()->create();
        ContentPermission::query()->create([
            'user_id' => $staff->id,
            'resource' => ContentResource::Projects,
        ]);

        $this->actingAs($staff)
            ->post(route('dashboard.projects.store'), [
                'title' => 'Harbour Pavilion',
                'slug' => 'harbour-pavilion',
            ])
            ->assertRedirect();

        $project = Project::query()->where('slug', 'harbour-pavilion')->firstOrFail();
        $this->assertSame($staff->id, $project->created_by);
        $this->assertSame(Status::Active, $project->status);
        $this->assertFalse($project->featured);
        $this->assertSame(1, $project->featured_order);

        $this->actingAs($staff)
            ->delete(route('dashboard.projects.destroy', $project))
            ->assertRedirect(route('dashboard.projects.index'));

        $this->assertSoftDeleted($project);
        $this->assertSame($staff->id, $project->fresh()->deleted_by);
        $this->get('/project-detail?slug=harbour-pavilion')->assertNotFound();
    }

    public function test_admin_can_create_staff_and_assign_permissions(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post(route('dashboard.staff.store'), [
                'name' => 'Editor',
                'email' => 'editor@azoogi.com',
                'password' => '12345678',
                'password_confirmation' => '12345678',
                'resources' => [ContentResource::Contact->value],
            ])
            ->assertRedirect();

        $staff = User::query()->where('email', 'editor@azoogi.com')->firstOrFail();
        $this->assertTrue($staff->isStaff());
        $this->assertTrue($staff->isActive());
        $this->assertTrue($staff->canManage('contact'));
        $this->assertFalse($staff->canManage('home'));
    }

    public function test_page_editor_shows_the_live_frontend_preview(): void
    {
        $this->seed([AdminUserSeeder::class, PageSeeder::class]);
        $admin = User::query()->where('email', 'admin@azoogi.com')->firstOrFail();

        $this->actingAs($admin)
            ->get('/dashboard/content/pages/home')
            ->assertOk()
            ->assertSee('dash-visual-frame', false)
            ->assertSee('Page Meta', false)
            ->assertSee('Live preview', false)
            ->assertSee('dash-drawer-meta', false)
            ->assertSee('name="meta_description"', false)
            ->assertDontSee('View live', false)
            ->assertSee('/dashboard/content/pages/home/preview', false);

        $this->actingAs($admin)
            ->get('/dashboard/content/pages/home/preview')
            ->assertOk()
            ->assertSee('data-cms-section="slide"', false)
            ->assertSee('cms-editor.js', false)
            ->assertSee('<base href="'.rtrim(url('/'), '/').'/">', false);

        $this->get('/')
            ->assertOk()
            ->assertDontSee('data-cms-section="slide"', false)
            ->assertDontSee('<base href=', false);
    }

    public function test_legal_pages_render_html_and_use_ckeditor_for_body(): void
    {
        $this->seed([AdminUserSeeder::class, PageSeeder::class]);
        $admin = User::query()->where('email', 'admin@azoogi.com')->firstOrFail();

        $this->get('/privacy')
            ->assertOk()
            ->assertSee('<h3>', false)
            ->assertSee('Who We Are', false)
            ->assertDontSee('&lt;h3&gt;', false);

        $this->actingAs($admin)
            ->get('/dashboard/content/pages/privacy')
            ->assertOk()
            ->assertSee('data-ckeditor', false)
            ->assertSee('ckeditor', false);
    }

    public function test_audience_pages_have_dedicated_editors(): void
    {
        $this->seed([AdminUserSeeder::class, PageSeeder::class]);
        $admin = User::query()->where('email', 'admin@azoogi.com')->firstOrFail();

        $this->actingAs($admin)
            ->get('/dashboard/content/pages')
            ->assertOk()
            ->assertSee('Home Owner', false)
            ->assertSee('Architect / Designer', false)
            ->assertSee('Electrician / Builder', false)
            ->assertSee('Wholesaler', false)
            ->assertDontSee('content/pages/audience', false);

        $this->actingAs($admin)
            ->get('/dashboard/content/pages/architect-designer/preview')
            ->assertOk()
            ->assertSee('data-cms-section="hero"', false)
            ->assertSee('For Interior', false);
    }

    public function test_staff_can_only_preview_assigned_pages(): void
    {
        $this->seed(PageSeeder::class);

        $staff = User::factory()->staff()->create();
        ContentPermission::query()->create([
            'user_id' => $staff->id,
            'resource' => ContentResource::About,
        ]);

        $this->actingAs($staff)->get('/dashboard/content/pages/about/preview')->assertOk();
        $this->actingAs($staff)->get('/dashboard/content/pages/home/preview')->assertForbidden();
    }

    public function test_admin_can_toggle_project_status_and_featured_from_the_table(): void
    {
        $admin = User::factory()->admin()->create();
        $project = Project::factory()->create([
            'status' => Status::Active,
            'featured' => false,
        ]);

        $this->actingAs($admin)
            ->patchJson(route('dashboard.projects.toggle-status', $project))
            ->assertOk()
            ->assertJsonPath('on', false)
            ->assertJsonPath('label', 'Inactive');

        $this->assertSame(Status::Inactive, $project->fresh()->status);

        $this->actingAs($admin)
            ->patchJson(route('dashboard.projects.toggle-featured', $project))
            ->assertOk()
            ->assertJsonPath('on', true)
            ->assertJsonPath('label', 'Yes');

        $this->assertTrue($project->fresh()->featured);
    }

    public function test_admin_can_toggle_staff_status_from_the_table(): void
    {
        $admin = User::factory()->admin()->create();
        $staff = User::factory()->staff()->create(['status' => Status::Active]);

        $this->actingAs($admin)
            ->get('/dashboard/staff')
            ->assertOk()
            ->assertSee(route('dashboard.staff.toggle-status', $staff), false);

        $this->actingAs($admin)
            ->get('/dashboard/staff/create')
            ->assertOk()
            ->assertDontSee('name="status"', false);

        $this->actingAs($admin)
            ->get(route('dashboard.staff.edit', $staff))
            ->assertOk()
            ->assertDontSee('name="status"', false);

        $this->actingAs($admin)
            ->patchJson(route('dashboard.staff.toggle-status', $staff))
            ->assertOk()
            ->assertJsonPath('on', false)
            ->assertJsonPath('label', 'Inactive');

        $this->assertSame(Status::Inactive, $staff->fresh()->status);

        $this->actingAs($admin)
            ->put(route('dashboard.staff.update', $staff), [
                'name' => $staff->name,
                'email' => $staff->email,
                'status' => Status::Active->value,
            ])
            ->assertRedirect();

        $this->assertSame(Status::Inactive, $staff->fresh()->status);
    }

    public function test_staff_password_requires_confirmation(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->from(route('dashboard.staff.create'))
            ->post(route('dashboard.staff.store'), [
                'name' => 'Editor',
                'email' => 'editor@azoogi.com',
                'password' => '12345678',
                'password_confirmation' => 'mismatch',
            ])
            ->assertRedirect(route('dashboard.staff.create'))
            ->assertSessionHasErrors('password');

        $staff = User::factory()->staff()->create();

        $this->actingAs($admin)
            ->from(route('dashboard.staff.edit', $staff))
            ->put(route('dashboard.staff.update', $staff), [
                'name' => $staff->name,
                'email' => $staff->email,
                'password' => 'new-password',
                'password_confirmation' => 'mismatch',
            ])
            ->assertRedirect(route('dashboard.staff.edit', $staff))
            ->assertSessionHasErrors('password');
    }

    public function test_new_project_gets_the_next_featured_order_and_forms_omit_featured_fields(): void
    {
        $admin = User::factory()->admin()->create();
        Project::factory()->create(['featured_order' => 4]);
        Project::factory()->create(['featured_order' => 7]);

        $this->actingAs($admin)
            ->get('/dashboard/content/projects/create')
            ->assertOk()
            ->assertDontSee('name="featured"', false)
            ->assertDontSee('name="featured_order"', false)
            ->assertDontSee('name="status"', false);

        $this->actingAs($admin)
            ->post(route('dashboard.projects.store'), [
                'title' => 'North Annex',
                'slug' => 'north-annex',
                'status' => Status::Inactive->value,
                'featured' => '1',
                'featured_order' => 1,
            ])
            ->assertRedirect();

        $project = Project::query()->where('slug', 'north-annex')->firstOrFail();
        $this->assertFalse($project->featured);
        $this->assertSame(8, $project->featured_order);
        $this->assertSame(Status::Active, $project->status);
    }

    public function test_updating_a_project_does_not_change_featured_state_or_order(): void
    {
        $admin = User::factory()->admin()->create();
        $project = Project::factory()->featured(3)->inactive()->create([
            'title' => 'Kept Featured',
            'slug' => 'kept-featured',
        ]);

        $this->actingAs($admin)
            ->get(route('dashboard.projects.edit', $project))
            ->assertOk()
            ->assertDontSee('name="featured"', false)
            ->assertDontSee('name="featured_order"', false)
            ->assertDontSee('name="status"', false);

        $this->actingAs($admin)
            ->put(route('dashboard.projects.update', $project), [
                'title' => 'Kept Featured Updated',
                'slug' => 'kept-featured',
                'status' => Status::Active->value,
                'featured' => '0',
                'featured_order' => 99,
            ])
            ->assertRedirect();

        $project->refresh();
        $this->assertSame('Kept Featured Updated', $project->title);
        $this->assertTrue($project->featured);
        $this->assertSame(3, $project->featured_order);
        $this->assertSame(Status::Inactive, $project->status);
    }

    public function test_projects_can_be_reordered_from_the_index(): void
    {
        $admin = User::factory()->admin()->create();
        $first = Project::factory()->create(['title' => 'Alpha', 'featured_order' => 1]);
        $second = Project::factory()->create(['title' => 'Beta', 'featured_order' => 2]);
        $third = Project::factory()->create(['title' => 'Gamma', 'featured_order' => 3]);

        $this->actingAs($admin)
            ->get('/dashboard/content/projects')
            ->assertOk()
            ->assertSee('data-dash-sort', false)
            ->assertSee('dash-drag-handle', false);

        $this->actingAs($admin)
            ->patchJson(route('dashboard.projects.reorder'), [
                'order' => [$third->id, $first->id, $second->id],
            ])
            ->assertOk()
            ->assertJsonPath('message', 'Featured order updated.');

        $this->assertSame(1, $third->fresh()->featured_order);
        $this->assertSame(2, $first->fresh()->featured_order);
        $this->assertSame(3, $second->fresh()->featured_order);
    }

    public function test_staff_can_toggle_assigned_page_status(): void
    {
        $this->seed(PageSeeder::class);

        $staff = User::factory()->staff()->create();
        ContentPermission::query()->create([
            'user_id' => $staff->id,
            'resource' => ContentResource::About,
        ]);

        $page = Page::query()->where('slug', 'about')->firstOrFail();

        $this->actingAs($staff)
            ->patchJson(route('dashboard.pages.toggle-status', $page))
            ->assertOk()
            ->assertJsonPath('on', false);

        $this->assertSame(Status::Inactive, $page->fresh()->status);

        $this->actingAs($staff)
            ->patchJson(route('dashboard.pages.toggle-status', 'home'))
            ->assertForbidden();
    }

    public function test_sidebar_shows_settings_and_logout_beside_the_role(): void
    {
        $admin = User::factory()->admin()->create(['name' => 'Pat']);

        $html = $this->actingAs($admin)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('aria-label="Settings"', false)
            ->assertSee('aria-label="Log out"', false)
            ->assertSee(route('dashboard.profile.edit'), false)
            ->getContent();

        $role = strpos($html, '>Admin</span>');
        $settings = strpos($html, 'aria-label="Settings"');
        $logout = strpos($html, 'aria-label="Log out"');

        $this->assertNotFalse($role);
        $this->assertNotFalse($settings);
        $this->assertNotFalse($logout);
        $this->assertTrue($role < $settings);
        $this->assertTrue($settings < $logout);
    }

    public function test_authenticated_users_can_update_profile_and_password(): void
    {
        $user = User::factory()->staff()->create([
            'name' => 'Editor',
            'email' => 'editor@azoogi.com',
            'password' => '12345678',
        ]);

        $this->actingAs($user)
            ->get('/dashboard/settings')
            ->assertOk()
            ->assertSee('Settings', false)
            ->assertSee('New password', false)
            ->assertSee('Confirm password', false);

        $this->actingAs($user)
            ->put(route('dashboard.profile.update'), [
                'name' => 'Pat Editor',
                'email' => 'pat.editor@azoogi.com',
                'current_password' => '12345678',
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ])
            ->assertRedirect(route('dashboard.profile.edit'));

        $user->refresh();

        $this->assertSame('Pat Editor', $user->name);
        $this->assertSame('pat.editor@azoogi.com', $user->email);
        $this->assertTrue(Hash::check('new-password', $user->password));
    }

    public function test_password_change_requires_the_current_password(): void
    {
        $user = User::factory()->admin()->create(['password' => '12345678']);

        $this->actingAs($user)
            ->from(route('dashboard.profile.edit'))
            ->put(route('dashboard.profile.update'), [
                'name' => $user->name,
                'email' => $user->email,
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ])
            ->assertRedirect(route('dashboard.profile.edit'))
            ->assertSessionHasErrors('current_password');
    }

    public function test_profile_password_requires_confirmation(): void
    {
        $user = User::factory()->admin()->create(['password' => '12345678']);

        $this->actingAs($user)
            ->from(route('dashboard.profile.edit'))
            ->put(route('dashboard.profile.update'), [
                'name' => $user->name,
                'email' => $user->email,
                'current_password' => '12345678',
                'password' => 'new-password',
                'password_confirmation' => 'mismatch',
            ])
            ->assertRedirect(route('dashboard.profile.edit'))
            ->assertSessionHasErrors('password');
    }

    public function test_staff_can_edit_assigned_header_section(): void
    {
        $this->seed(PageSeeder::class);

        $staff = User::factory()->staff()->create();
        ContentPermission::query()->create([
            'user_id' => $staff->id,
            'resource' => ContentResource::Header,
        ]);

        $this->actingAs($staff)->get('/dashboard/content/sections')->assertOk()->assertSee('Header', false)->assertDontSee('Footer', false);
        $this->actingAs($staff)->get('/dashboard/content/sections/header')->assertOk();
        $this->actingAs($staff)->get('/dashboard/content/sections/footer')->assertForbidden();
        $this->actingAs($staff)->get('/dashboard/content/pages')->assertForbidden();
    }

    public function test_admin_can_update_header_rotating_text(): void
    {
        $this->seed([AdminUserSeeder::class, PageSeeder::class]);
        $admin = User::query()->where('email', 'admin@azoogi.com')->firstOrFail();
        $header = Page::query()->where('slug', 'header')->firstOrFail();
        $word = PageMeta::query()
            ->where('page_id', $header->id)
            ->where('key', 'header.word.text')
            ->where('sort_order', 0)
            ->firstOrFail();

        $this->actingAs($admin)
            ->get(route('dashboard.sections.edit', $header))
            ->assertOk()
            ->assertSee('Rotating text', false)
            ->assertSee('DESIGN', false)
            ->assertDontSee('Item 1', false);

        $this->actingAs($admin)
            ->put(route('dashboard.sections.update', $header), [
                'title' => $header->title,
                'meta_description' => $header->meta_description,
                'status' => Status::Active->value,
                'meta' => [
                    $word->id => ['value' => 'CRAFT'],
                ],
            ])
            ->assertRedirect(route('dashboard.sections.edit', $header));

        $this->get('/')
            ->assertOk()
            ->assertSee('"CRAFT"', false)
            ->assertDontSee('"DESIGN"', false);
    }

    public function test_admin_can_update_header_and_footer_copy(): void
    {
        $this->seed([AdminUserSeeder::class, PageSeeder::class]);
        $admin = User::query()->where('email', 'admin@azoogi.com')->firstOrFail();
        $header = Page::query()->where('slug', 'header')->firstOrFail();
        $footer = Page::query()->where('slug', 'footer')->firstOrFail();
        $headerDescription = PageMeta::query()->where('page_id', $header->id)->where('key', 'header.description')->firstOrFail();
        $footerDescription = PageMeta::query()->where('page_id', $footer->id)->where('key', 'footer.description')->firstOrFail();

        $this->actingAs($admin)
            ->put(route('dashboard.sections.update', $header), [
                'title' => $header->title,
                'meta_description' => $header->meta_description,
                'status' => Status::Active->value,
                'meta' => [
                    $headerDescription->id => ['value' => 'Updated header tagline'],
                ],
            ])
            ->assertRedirect(route('dashboard.sections.edit', $header));

        $this->actingAs($admin)
            ->put(route('dashboard.sections.update', $footer), [
                'title' => $footer->title,
                'meta_description' => $footer->meta_description,
                'status' => Status::Active->value,
                'meta' => [
                    $footerDescription->id => ['value' => 'Updated footer description'],
                ],
            ])
            ->assertRedirect(route('dashboard.sections.edit', $footer));

        $this->get('/')
            ->assertOk()
            ->assertSee('Updated header tagline', false)
            ->assertSee('Updated footer description', false);
    }

    public function test_admin_can_update_page_meta_details(): void
    {
        $this->seed([AdminUserSeeder::class, PageSeeder::class]);
        $admin = User::query()->where('email', 'admin@azoogi.com')->firstOrFail();
        $page = Page::query()->where('slug', 'about')->firstOrFail();

        $this->actingAs($admin)
            ->put(route('dashboard.pages.update', $page), [
                'title' => 'About meta title',
                'meta_description' => 'About meta description for search.',
                'status' => Status::Active->value,
                'editor_section' => 'meta',
            ])
            ->assertRedirect(route('dashboard.pages.edit', ['page' => $page, 'section' => 'meta']));

        $this->get('/about')
            ->assertOk()
            ->assertSee('<title>About meta title</title>', false)
            ->assertSee('content="About meta description for search."', false);
    }
}
