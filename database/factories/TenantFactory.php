<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class TenantFactory extends Factory
{
    protected $model = Tenant::class;

    public function definition(): array
    {
        $name = $this->faker->company();

        return [
            'name'   => $name,
            'slug'   => Str::slug($name) . '-' . $this->faker->unique()->numberBetween(1, 9999),
            'status' => 'active',
            'plan'   => 'standard',
        ];
    }

    public function active(): static
    {
        return $this->state(['status' => 'active']);
    }

    public function trial(): static
    {
        return $this->state(['status' => 'trial']);
    }

    public function suspended(): static
    {
        return $this->state(['status' => 'suspended']);
    }
}
