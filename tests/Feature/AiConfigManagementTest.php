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
        $response->assertSee('AI Models, Providers &amp; Rates', false);
        $response->assertSee('Anthropic Claude');
        $response->assertSee('OpenRouter');
        $response->assertSee('Google Gemini');
        $response->assertSee('+ Add AI Provider');
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

        $updateResponse = $this->actingAs($this->adminUser)->put(route('dashboard.ai.widget.update'), [
            'ai_name' => 'Azoogi Lighting Consultant',
            'ai_avatar' => 'lightbulb',
            'ai_subtitle' => 'Sydney Commercial Lighting Specialist',
            'startup_message' => 'Hello! Welcome to Azoogi. How can we help you explore architectural luminaires today?',
            'lead_greeting_template' => 'Hi {name}! Thanks for reaching out regarding {project}.',
            'intake_enabled' => '1',
            'intake_require_project' => '1',
            'starter_chips' => [
                ['icon' => '🌿', 'label' => 'Garden Spikes', 'prompt' => 'Show garden spike lights'],
                ['icon' => '🏢', 'label' => 'Linear Profiles', 'prompt' => 'Show architectural linear profiles'],
            ],
        ]);

        $updateResponse->assertRedirect(route('dashboard.ai.widget'));
        $updateResponse->assertSessionHas('status');

        $branding = ChatOrchestrator::getWidgetBranding();
        $this->assertSame('Azoogi Lighting Consultant', $branding['ai_name']);
        $this->assertSame('lightbulb', $branding['ai_avatar']);
        $this->assertSame('Sydney Commercial Lighting Specialist', $branding['ai_subtitle']);
        $this->assertTrue($branding['intake_require_project']);
        $this->assertCount(2, $branding['starter_chips']);
    }

    public function test_admin_can_view_and_manage_knowledge_rules_and_faqs(): void
    {
        $viewResponse = $this->actingAs($this->adminUser)->get(route('dashboard.ai.knowledge'));
        $viewResponse->assertOk();
        $viewResponse->assertSee('AI Ruleset, Context &amp; FAQ Knowledge Base', false);

        $updateResponse = $this->actingAs($this->adminUser)->put(route('dashboard.ai.knowledge.update'), [
            'ruleset' => "- Always recommend 3000K warm white for luxury hospitality.\n- Check AS/NZS standards.",
            'company_context' => "- Fast dispatch from Sydney warehouse.\n- 5-year warranty on all fixtures.",
        ]);

        $updateResponse->assertRedirect(route('dashboard.ai.knowledge'));
        $updateResponse->assertSessionHas('status');

        $knowledge = ChatOrchestrator::getKnowledgeRules();
        $this->assertStringContainsString('luxury hospitality', $knowledge['ruleset']);
        $this->assertStringContainsString('Sydney warehouse', $knowledge['company_context']);

        // Add FAQ Item
        $faqResponse = $this->actingAs($this->adminUser)->post(route('dashboard.ai.faqs.store'), [
            'category' => 'Shipping & Lead Time',
            'question' => 'How long does custom linear profile cutting take?',
            'answer' => 'Standard turnaround is 3-5 business days from our Sydney warehouse.',
            'is_active' => '1',
        ]);

        $faqResponse->assertRedirect(route('dashboard.ai.knowledge'));
        $faqResponse->assertSessionHas('status');

        $faqs = ChatOrchestrator::getFaqs();
        $this->assertNotEmpty($faqs);

        $latestFaq = end($faqs);
        $this->assertSame('How long does custom linear profile cutting take?', $latestFaq['question']);

        // Check system prompt compiles with custom knowledge and FAQs
        $systemPrompt = app(ChatOrchestrator::class)->getSystemPrompt();
        $this->assertStringContainsString('luxury hospitality', $systemPrompt);
        $this->assertStringContainsString('How long does custom linear profile cutting take?', $systemPrompt);

        // Delete FAQ Item
        $delResponse = $this->actingAs($this->adminUser)->delete(route('dashboard.ai.faqs.delete', $latestFaq['id']));
        $delResponse->assertRedirect(route('dashboard.ai.knowledge'));
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
