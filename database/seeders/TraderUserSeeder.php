<?php

namespace Database\Seeders;

use App\Enums\Status;
use App\Enums\UserType;
use App\Models\User;
use Illuminate\Database\Seeder;

class TraderUserSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'trader@azoogi.com'],
            [
                'name' => 'Trader',
                'password' => '12345678',
                'user_type' => UserType::Trader,
                'status' => Status::Active,
                'email_verified_at' => now(),
            ],
        );
    }
}
