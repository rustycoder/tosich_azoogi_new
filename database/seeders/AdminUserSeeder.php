<?php

namespace Database\Seeders;

use App\Enums\Status;
use App\Enums\UserType;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->firstOrCreate(
            ['email' => 'admin@azoogi.com'],
            [
                'name' => 'Admin',
                'password' => '12345678',
                'user_type' => UserType::Admin,
                'status' => Status::Active,
                'email_verified_at' => now(),
            ],
        );
    }
}
