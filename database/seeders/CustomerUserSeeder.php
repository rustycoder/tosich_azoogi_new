<?php

namespace Database\Seeders;

use App\Enums\Status;
use App\Enums\UserType;
use App\Models\User;
use Illuminate\Database\Seeder;

class CustomerUserSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'customer@azoogi.com'],
            [
                'name' => 'Customer',
                'password' => '12345678',
                'user_type' => UserType::Customer,
                'status' => Status::Active,
                'email_verified_at' => now(),
            ],
        );
    }
}
