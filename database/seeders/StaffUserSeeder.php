<?php

namespace Database\Seeders;

use App\Enums\Status;
use App\Enums\UserType;
use App\Models\User;
use Illuminate\Database\Seeder;

class StaffUserSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->firstOrCreate(
            ['email' => 'staff@azoogi.com'],
            [
                'name' => 'Staff',
                'password' => '12345678',
                'user_type' => UserType::Staff,
                'status' => Status::Active,
                'email_verified_at' => now(),
            ],
        );
    }
}
