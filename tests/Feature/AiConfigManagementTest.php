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

    public function test_admin_can_view_ai_configuration_menu_page(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('dashboard.ai.config'));

        $response->assertOk();
        $response->assertSee('AI Configuration');
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

        $response->assertRedirect(route('dashboard.ai.config'));
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

        $response->assertRedirect(route('dashboard.ai.config'));
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
        // Seed a custom provider first
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

        $response->assertRedirect(route('dashboard.ai.config'));
        $response->assertSessionHas('status');

        $config = ChatOrchestrator::getActiveAiConfig();
        $this->assertArrayNotHasKey('custom_groq_123', $config['custom_providers']);
        $this->assertSame('anthropic', $config['driver']); // Fallbacks when active was deleted
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

    public function test_update_ai_config_rejects_unsupported_driver(): void
    {
        $response = $this->actingAs($this->adminUser)->put(route('dashboard.ai.update'), [
            'driver' => 'invalid_driver_name',
        ]);

        $response->assertSessionHasErrors(['driver']);
    }
}
