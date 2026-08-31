<?php

namespace Tests\Feature;

use App\Enums\Status;
use App\Enums\UserType;
use App\Models\User;
use Database\Seeders\AdminUserSeeder;
use Database\Seeders\CustomerUserSeeder;
use Database\Seeders\StaffUserSeeder;
use Database\Seeders\TraderUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_renders_without_site_navigation(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('Sign in', false)
            ->assertDontSee('LED Calculator', false)
            ->assertDontSee('Trade Login', false);
    }

    /**
     * @return array<string, array{0: class-string, 1: string, 2: UserType}>
     */
    public static function roleSeederProvider(): array
    {
        return [
            'admin' => [AdminUserSeeder::class, 'admin@azoogi.com', UserType::Admin],
            'staff' => [StaffUserSeeder::class, 'staff@azoogi.com', UserType::Staff],
            'customer' => [CustomerUserSeeder::class, 'customer@azoogi.com', UserType::Customer],
            'trader' => [TraderUserSeeder::class, 'trader@azoogi.com', UserType::Trader],
        ];
    }

    #[DataProvider('roleSeederProvider')]
    public function test_role_seeder_creates_the_expected_user(string $seeder, string $email, UserType $type): void
    {
        $this->seed($seeder);

        $user = User::query()->where('email', $email)->first();

        $this->assertNotNull($user);
        $this->assertSame($type, $user->user_type);
        $this->assertSame(Status::Active, $user->status);
    }

    #[DataProvider('roleSeederProvider')]
    public function test_each_role_can_sign_in(string $seeder, string $email, UserType $type): void
    {
        $this->seed($seeder);

        $this->from('/login')
            ->post('/login', [
                'email' => $email,
                'password' => '12345678',
            ])
            ->assertRedirect('/dashboard');

        $this->assertAuthenticated();
        $this->assertTrue(auth()->user()->user_type === $type);
    }

    public function test_invalid_credentials_are_rejected(): void
    {
        $this->seed(AdminUserSeeder::class);

        $this->from('/login')
            ->post('/login', [
                'email' => 'admin@azoogi.com',
                'password' => 'wrong-password',
            ])
            ->assertRedirect('/login')
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_inactive_users_cannot_sign_in(): void
    {
        $user = User::factory()->inactive()->create([
            'email' => 'inactive@azoogi.com',
            'password' => '12345678',
        ]);

        $this->from('/login')
            ->post('/login', [
                'email' => $user->email,
                'password' => '12345678',
            ])
            ->assertRedirect('/login')
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }
}
