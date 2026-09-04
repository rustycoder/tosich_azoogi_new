<?php

namespace Database\Factories;

use App\Enums\EnquiryStatus;
use App\Enums\EnquiryType;
use App\Models\Enquiry;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Enquiry>
 */
class EnquiryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'type' => EnquiryType::Contact,
            'status' => EnquiryStatus::Pending,
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'phone' => fake()->numerify('04## ### ###'),
            'company' => fake()->company(),
            'message' => fake()->sentence(),
            'payload' => [],
        ];
    }

    public function quote(): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => EnquiryType::Quote,
            'payload' => [
                'products' => 'Garden Light (GL005) x1',
                'role' => 'I’m an Architect',
                'method' => 'Email',
            ],
        ]);
    }

    public function product(): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => EnquiryType::Product,
            'payload' => [
                'project' => 'Sydney office fitout',
                'specification' => "Product: Garden Light (Garden Light)\nVariant Model: GL005",
            ],
        ]);
    }

    public function contact(): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => EnquiryType::Contact,
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => EnquiryStatus::Pending,
        ]);
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => EnquiryStatus::Active,
        ]);
    }

    public function done(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => EnquiryStatus::Done,
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => EnquiryStatus::Cancelled,
        ]);
    }
}
