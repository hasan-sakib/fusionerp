<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductFactory extends Factory
{
    public function definition(): array
    {
        $name = $this->faker->unique()->words(3, true);

        return [
            'category_id'     => null,
            'name'            => ucwords($name),
            'slug'            => Str::slug($name) . '-' . $this->faker->unique()->numberBetween(1000, 9999),
            'sku'             => strtoupper($this->faker->unique()->bothify('??-####')),
            'barcode'         => $this->faker->optional()->ean13(),
            'description'     => $this->faker->optional()->sentence(),
            'price'           => $this->faker->randomFloat(2, 1, 999),
            'cost'            => $this->faker->optional()->randomFloat(2, 1, 500),
            'stock_quantity'  => $this->faker->numberBetween(0, 500),
            'min_stock_level' => 5,
            'image'           => null,
            'status'          => 'active',
            'is_featured'     => false,
        ];
    }

    public function inactive(): static
    {
        return $this->state(['status' => 'inactive']);
    }

    public function draft(): static
    {
        return $this->state(['status' => 'draft']);
    }

    public function outOfStock(): static
    {
        return $this->state(['stock_quantity' => 0]);
    }

    public function withCategory(): static
    {
        return $this->state(['category_id' => Category::factory()]);
    }
}
