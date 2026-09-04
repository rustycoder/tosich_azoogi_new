<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);
        $category = fake()->randomElement(['NEON', 'Profiles', 'Garden Light']);

        return [
            'airtable_id' => 'rec'.fake()->unique()->bothify('??????????????'),
            'product_name' => $name,
            'category' => $category,
            'status' => 'publish',
            'sort_order' => fake()->numberBetween(1, 20),
            'cover' => '/assets/img/neon.webp',
            'categories' => [$category],
            'category_path' => [$category],
            'product_images' => ['/assets/img/neon.webp'],
        ];
    }
}
