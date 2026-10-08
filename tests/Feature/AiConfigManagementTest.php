<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\LlmFeed;
use App\Models\User;
use App\Services\Chat\ChatOrchestrator;
use App\Services\Chat\Drivers\AnthropicDriver;
use App\Services\Chat\Drivers\GeminiDriver;
use App\Services\Chat\Drivers\OpenAiDriver;
use Database\Seeders\PageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AiConfigManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PageSeeder::class);

        $this->adminUser = User::factory()->admin()->create();
    }

    public function test_admin_can_view_ai_models_menu_page(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('dashboard.ai.models'));

        $response->assertOk();
        $response->assertSee('AI Models &amp; Providers', false);
        $response->assertSee('Anthropic Claude');
        $response->assertSee('OpenRouter');
        $response->assertSee('Google Gemini');
        $response->assertSee('Save Model Settings');
        $response->assertSee('Add Custom AI Provider');
    }

    public function test_admin_can_view_ai_rates_menu_page(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('dashboard.ai.rates'));

        $response->assertOk();
        $response->assertSee('AI Rates &amp; Token Pricing', false);
        $response->assertSee('Standard Token Rate Schedule');
        $response->assertSee('Live Cost Estimator Playground');
        $response->assertSee('Claude 3.5 Sonnet');
    }

    public function test_admin_can_view_ai_prompt_page(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('dashboard.ai.prompt'));

        $response->assertOk();
        $response->assertSee('AI Live System Prompt Inspector');
        $response->assertSee('Full Compiled System Prompt');
        $response->assertSee('System Rules');
        $response->assertSee('Company Context');
        $response->assertSee('FAQ Knowledge');
        $response->assertSee('Copy Full Prompt');
    }

    public function test_admin_can_update_ai_driver_models_and_api_keys_in_database(): void
    {
        $response = $this->actingAs($this->adminUser)->put(route('dashboard.ai.update'), [
            'driver' => 'anthropic',
            'anthropic_api_key' => 'sk-ant-test-backend-key-12345',
            'anthropic_model' => 'claude-3-5-sonnet-20241022',
            'openrouter_api_key' => 'sk-or-test-backend-key-67890',
            'openrouter_model' => 'anthropic/claude-3.5-sonnet',
            'gemini_api_key' => 'AIzaSyTestBackendKey999',
            'gemini_model' => 'gemini-2.5-flash',
            'openai_model' => 'gpt-4o-mini',
        ]);

        $response->assertRedirect(route('dashboard.ai.models'));
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('llm_feeds', [
            'key' => 'ai_chat_config',
        ]);

        $config = ChatOrchestrator::getActiveAiConfig();
        $this->assertSame('anthropic', $config['driver']);
        $this->assertSame('sk-ant-test-backend-key-12345', $config['anthropic_api_key']);
        $this->assertSame('claude-3-5-sonnet-20241022', $config['anthropic_model']);
        $this->assertSame('sk-or-test-backend-key-67890', $config['openrouter_api_key']);
        $this->assertSame('AIzaSyTestBackendKey999', $config['gemini_api_key']);
    }

    public function test_admin_can_add_custom_ai_provider(): void
    {
        $response = $this->actingAs($this->adminUser)->post(route('dashboard.ai.custom-provider.store'), [
            'name' => 'DeepSeek AI',
            'type' => 'openai',
            'base_url' => 'https://api.deepseek.com/v1',
            'api_key' => 'sk-deepseek-test-key-9988',
            'model' => 'deepseek-chat',
            'description' => 'DeepSeek chat reasoning model',
            'set_active' => '1',
        ]);

        $response->assertRedirect(route('dashboard.ai.models'));
        $response->assertSessionHas('status');

        $config = ChatOrchestrator::getActiveAiConfig();
        $this->assertNotEmpty($config['custom_providers']);

        $customProviderKey = array_key_first($config['custom_providers']);
        $this->assertStringStartsWith('custom_deepseek_ai_', $customProviderKey);
        $this->assertSame('DeepSeek AI', $config['custom_providers'][$customProviderKey]['name']);
        $this->assertSame('https://api.deepseek.com/v1', $config['custom_providers'][$customProviderKey]['base_url']);
        $this->assertSame('deepseek-chat', $config['custom_providers'][$customProviderKey]['model']);
        $this->assertSame($customProviderKey, $config['driver']);

        $driverInstance = ChatOrchestrator::makeDriver($customProviderKey);
        $this->assertInstanceOf(OpenAiDriver::class, $driverInstance);
    }

    public function test_admin_can_add_custom_ai_provider_with_multiple_models_and_switch_active_model(): void
    {
        $response = $this->actingAs($this->adminUser)->post(route('dashboard.ai.custom-provider.store'), [
            'name' => 'Groq Fast Cloud',
            'type' => 'openai',
            'base_url' => 'https://api.groq.com/openai/v1',
            'api_key' => 'gsk_test_multi_model_123',
            'model' => 'llama-3.3-70b-versatile, mixtral-8x7b-32768, gemma2-9b-it',
            'description' => 'Groq ultra fast multi-model inference',
            'set_active' => '1',
        ]);

        $response->assertRedirect(route('dashboard.ai.models'));
        $response->assertSessionHas('status');

        $config = ChatOrchestrator::getActiveAiConfig();
        $customProviderKey = array_key_first($config['custom_providers']);
        $this->assertStringStartsWith('custom_groq_fast_cloud_', $customProviderKey);

        $cpData = $config['custom_providers'][$customProviderKey];
        $this->assertSame('llama-3.3-70b-versatile', $cpData['model']);
        $this->assertEquals(['llama-3.3-70b-versatile', 'mixtral-8x7b-32768', 'gemma2-9b-it'], $cpData['models']);

        // Test switching the active model of the custom provider in config
        $updateResponse = $this->actingAs($this->adminUser)->put(route('dashboard.ai.update'), [
            'driver' => $customProviderKey,
            'custom_provider_models' => [
                $customProviderKey => 'mixtral-8x7b-32768',
            ],
        ]);

        $updateResponse->assertRedirect(route('dashboard.ai.models'));
        $updatedConfig = ChatOrchestrator::getActiveAiConfig();
        $this->assertSame('mixtral-8x7b-32768', $updatedConfig['custom_providers'][$customProviderKey]['model']);
    }

    public function test_admin_can_delete_custom_ai_provider(): void
    {
        $configPayload = [
            'driver' => 'custom_groq_123',
            'custom_providers' => [
                'custom_groq_123' => [
                    'id' => 'custom_groq_123',
                    'name' => 'Groq Cloud',
                    'type' => 'openai',
                    'base_url' => 'https://api.groq.com/openai/v1',
                    'api_key' => 'gsk_test123',
                    'model' => 'llama-3.3-70b-versatile',
                ],
            ],
        ];

        LlmFeed::query()->updateOrCreate(
            ['key' => 'ai_chat_config'],
            ['content' => json_encode($configPayload), 'is_custom' => true]
        );

        $response = $this->actingAs($this->adminUser)->delete(route('dashboard.ai.custom-provider.delete', 'custom_groq_123'));

        $response->assertRedirect(route('dashboard.ai.models'));
        $response->assertSessionHas('status');

        $config = ChatOrchestrator::getActiveAiConfig();
        $this->assertArrayNotHasKey('custom_groq_123', $config['custom_providers']);
        $this->assertSame('anthropic', $config['driver']);
    }

    public function test_admin_can_view_and_update_widget_branding(): void
    {
        $viewResponse = $this->actingAs($this->adminUser)->get(route('dashboard.ai.widget'));
        $viewResponse->assertOk();
        $viewResponse->assertSee('AI Widget &amp; Branding Customization', false);
        $viewResponse->assertSee('Assistant Persona &amp; Identity', false);
        $viewResponse->assertSee('Icon Specifications &amp; Requirements', false);

        $updateResponse = $this->actingAs($this->adminUser)->put(route('dashboard.ai.widget.update'), [
            'ai_name' => 'Azoogi Lighting Consultant',
            'ai_subtitle' => 'Sydney Commercial Lighting Specialist',
            'startup_message' => 'Hello! Welcome to Azoogi. How can we help you explore architectural luminaires today?',
            'lead_greeting_template' => 'Hi {name}! Thanks for reaching out regarding {project}.',
            'intake_enabled' => '1',
            'intake_require_project' => '1',
            'starter_chips' => [
                ['prompt' => 'Show garden spike lights'],
                ['prompt' => 'Show architectural linear profiles'],
            ],
        ]);

        $updateResponse->assertRedirect(route('dashboard.ai.widget'));
        $updateResponse->assertSessionHas('status');

        $branding = ChatOrchestrator::getWidgetBranding();
        $this->assertSame('Azoogi Lighting Consultant', $branding['ai_name']);
        $this->assertSame('Sydney Commercial Lighting Specialist', $branding['ai_subtitle']);
        $this->assertTrue($branding['intake_require_project']);
        $this->assertCount(2, $branding['starter_chips']);
        $this->assertSame('Show garden spike lights', $branding['starter_chips'][0]['prompt']);
    }

    public function test_admin_can_upload_custom_avatar_icon_file(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('custom_ai_avatar.png', 256, 256);

        $response = $this->actingAs($this->adminUser)->put(route('dashboard.ai.widget.update'), [
            'ai_name' => 'Azoogi Custom Bot',
            'ai_avatar_file' => $file,
            'ai_subtitle' => 'Specialist Assistant',
            'startup_message' => 'Hello from custom bot!',
            'intake_enabled' => '1',
        ]);

        $response->assertRedirect(route('dashboard.ai.widget'));
        $response->assertSessionHas('status');

        $branding = ChatOrchestrator::getWidgetBranding();
        $this->assertSame('custom', $branding['ai_avatar']);
        $this->assertNotEmpty($branding['ai_custom_avatar_url']);
        $this->assertStringStartsWith('/storage/ai/avatars/avatar_', $branding['ai_custom_avatar_url']);
    }

    public function test_admin_can_view_and_manage_rules_page(): void
    {
        $viewResponse = $this->actingAs($this->adminUser)->get(route('dashboard.ai.rules'));
        $viewResponse->assertOk();
        $viewResponse->assertSee('AI System Rules &amp; Behavioral Directives', false);
        $viewResponse->assertSee('Add Rule Item', false);
        $viewResponse->assertSee('Role &amp; Tone of Voice', false);

        $storeResponse = $this->actingAs($this->adminUser)->post(route('dashboard.ai.rules.store'), [
            'category' => 'Custom Directives',
            'title' => 'Residential Lighting Directives',
            'content' => 'Suggest 3000K warm white and CRI90+ for luxury residential projects.',
            'is_active' => '1',
        ]);

        $storeResponse->assertRedirect(route('dashboard.ai.rules'));
        $storeResponse->assertSessionHas('status');

        $rules = ChatOrchestrator::getRulesItems();
        $this->assertNotEmpty($rules);
        $latestRule = end($rules);
        $this->assertSame('Residential Lighting Directives', $latestRule['title']);
        $this->assertSame('Custom Directives', $latestRule['category']);

        $knowledge = ChatOrchestrator::getKnowledgeRules();
        $this->assertStringContainsString('Residential Lighting Directives', $knowledge['ruleset']);
        $this->assertStringContainsString('luxury residential', $knowledge['ruleset']);

        // Delete Rule
        $delResponse = $this->actingAs($this->adminUser)->delete(route('dashboard.ai.rules.delete', $latestRule['id']));
        $delResponse->assertRedirect(route('dashboard.ai.rules'));
    }

    public function test_admin_can_view_and_manage_context_page(): void
    {
        $viewResponse = $this->actingAs($this->adminUser)->get(route('dashboard.ai.context'));
        $viewResponse->assertOk();
        $viewResponse->assertSee('Company Context &amp; Manufacturing Policies', false);
        $viewResponse->assertSee('Add Context Item', false);
        $viewResponse->assertSee('Headquarters &amp; Sydney Assembly Facility', false);

        $storeResponse = $this->actingAs($this->adminUser)->post(route('dashboard.ai.context.store'), [
            'category' => 'Showroom & Support',
            'title' => 'Sydney Design Studio Visits',
            'content' => 'Sydney showroom and lighting lab open by appointment for architects.',
            'is_active' => '1',
        ]);

        $storeResponse->assertRedirect(route('dashboard.ai.context'));
        $storeResponse->assertSessionHas('status');

        $contextItems = ChatOrchestrator::getContextItems();
        $this->assertNotEmpty($contextItems);
        $latestItem = end($contextItems);
        $this->assertSame('Sydney Design Studio Visits', $latestItem['title']);
        $this->assertSame('Showroom & Support', $latestItem['category']);

        $knowledge = ChatOrchestrator::getKnowledgeRules();
        $this->assertStringContainsString('Sydney Design Studio Visits', $knowledge['company_context']);
        $this->assertStringContainsString('open by appointment', $knowledge['company_context']);

        // Delete Context Item
        $delResponse = $this->actingAs($this->adminUser)->delete(route('dashboard.ai.context.delete', $latestItem['id']));
        $delResponse->assertRedirect(route('dashboard.ai.context'));
    }

    public function test_admin_can_view_and_manage_faqs_page(): void
    {
        $viewResponse = $this->actingAs($this->adminUser)->get(route('dashboard.ai.faqs'));
        $viewResponse->assertOk();
        $viewResponse->assertSee('AI FAQ Knowledge Base', false);

        $faqResponse = $this->actingAs($this->adminUser)->post(route('dashboard.ai.faqs.store'), [
            'category' => 'Linear Profiles',
            'question' => 'What is the maximum single cut length for linear extrusions?',
            'answer' => 'Continuous profiles up to 3 meters can be fabricated in Sydney.',
            'is_active' => '1',
        ]);

        $faqResponse->assertRedirect(route('dashboard.ai.faqs'));
        $faqResponse->assertSessionHas('status');

        $faqs = ChatOrchestrator::getFaqs();
        $this->assertNotEmpty($faqs);
        $latestFaq = end($faqs);
        $this->assertSame('What is the maximum single cut length for linear extrusions?', $latestFaq['question']);

        // Delete FAQ
        $delResponse = $this->actingAs($this->adminUser)->delete(route('dashboard.ai.faqs.delete', $latestFaq['id']));
        $delResponse->assertRedirect(route('dashboard.ai.faqs'));
    }

    public function test_admin_can_toggle_rule_context_and_faq_status(): void
    {
        // 1. Create a Rule and Toggle Status
        $this->actingAs($this->adminUser)->post(route('dashboard.ai.rules.store'), [
            'category' => 'Testing',
            'title' => 'Toggle Test Rule',
            'content' => 'Rule directive content to toggle.',
            'is_active' => '1',
        ]);
        $rules = ChatOrchestrator::getRulesItems();
        $ruleId = end($rules)['id'];

        $toggleRuleRes = $this->actingAs($this->adminUser)->patch(route('dashboard.ai.rules.toggle-status', $ruleId));
        $toggleRuleRes->assertOk();
        $toggleRuleRes->assertJson(['on' => false, 'label' => 'Inactive']);

        $toggleRuleRes2 = $this->actingAs($this->adminUser)->patch(route('dashboard.ai.rules.toggle-status', $ruleId));
        $toggleRuleRes2->assertOk();
        $toggleRuleRes2->assertJson(['on' => true, 'label' => 'Active']);

        // 2. Create Context and Toggle Status
        $this->actingAs($this->adminUser)->post(route('dashboard.ai.context.store'), [
            'category' => 'Testing',
            'title' => 'Toggle Test Context',
            'content' => 'Context content to toggle.',
            'is_active' => '1',
        ]);
        $contextItems = ChatOrchestrator::getContextItems();
        $contextId = end($contextItems)['id'];

        $toggleCtxRes = $this->actingAs($this->adminUser)->patch(route('dashboard.ai.context.toggle-status', $contextId));
        $toggleCtxRes->assertOk();
        $toggleCtxRes->assertJson(['on' => false, 'label' => 'Inactive']);

        // 3. Create FAQ and Toggle Status
        $this->actingAs($this->adminUser)->post(route('dashboard.ai.faqs.store'), [
            'category' => 'Testing',
            'question' => 'Toggle FAQ Question?',
            'answer' => 'Answer to toggle.',
            'is_active' => '1',
        ]);
        $faqs = ChatOrchestrator::getFaqs();
        $faqId = end($faqs)['id'];

        $toggleFaqRes = $this->actingAs($this->adminUser)->patch(route('dashboard.ai.faqs.toggle-status', $faqId));
        $toggleFaqRes->assertOk();
        $toggleFaqRes->assertJson(['on' => false, 'label' => 'Inactive']);
    }

    public function test_chat_orchestrator_make_driver_instantiates_proper_driver_classes(): void
    {
        $anthropicDriver = ChatOrchestrator::makeDriver('anthropic', 'claude-3-5-sonnet-20241022');
        $this->assertInstanceOf(AnthropicDriver::class, $anthropicDriver);

        $openRouterDriver = ChatOrchestrator::makeDriver('openrouter', 'anthropic/claude-3.5-sonnet');
        $this->assertInstanceOf(OpenAiDriver::class, $openRouterDriver);

        $geminiDriver = ChatOrchestrator::makeDriver('gemini', 'gemini-2.5-flash');
        $this->assertInstanceOf(GeminiDriver::class, $geminiDriver);

        $openAiDriver = ChatOrchestrator::makeDriver('openai', 'gpt-4o');
        $this->assertInstanceOf(OpenAiDriver::class, $openAiDriver);
    }
}
