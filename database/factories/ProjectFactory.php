<?php

namespace Database\Factories;

use App\Enums\Status;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
{
    public function definition(): array
    {
        return [
            'slug' => fake()->unique()->slug(3),
            'title' => fake()->words(3, true),
            'tag' => fake()->randomElement(['Hospitality', 'Residential', 'Medical', 'Industrial']),
            'location' => fake()->city().' NSW',
            'type' => 'Commercial',
            'completed' => (string) fake()->year(),
            'featured' => false,
            'featured_order' => 0,
            'cover' => '/assets/img/img-0.jpg',
            'cover_remote' => null,
            'summary' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'gallery' => [],
            'status' => Status::Active,
        ];
    }

    public function featured(int $order = 1): static
    {
        return $this->state(fn (array $attributes) => [
            'featured' => true,
            'featured_order' => $order,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Status::Inactive,
        ]);
    }
}
