<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    public function definition(): array
    {
        $subtotal = $this->faker->randomFloat(2, 10, 500);

        return [
            'order_number'    => 'ORD-' . now()->format('Ym') . '-' . str_pad((string) $this->faker->unique()->numberBetween(1, 99999), 5, '0', STR_PAD_LEFT),
            'user_id'         => User::factory(),
            'customer_name'   => $this->faker->name(),
            'customer_email'  => $this->faker->safeEmail(),
            'customer_phone'  => $this->faker->optional()->phoneNumber(),
            'status'          => OrderStatus::Pending,
            'subtotal'        => $subtotal,
            'tax_rate'        => 0,
            'tax_amount'      => 0,
            'discount_amount' => 0,
            'total_amount'    => $subtotal,
            'notes'           => null,
        ];
    }

    public function pending(): static
    {
        return $this->state(['status' => OrderStatus::Pending]);
    }

    public function confirmed(): static
    {
        return $this->state(['status' => OrderStatus::Confirmed]);
    }

    public function completed(): static
    {
        return $this->state(['status' => OrderStatus::Completed]);
    }

    public function cancelled(): static
    {
        return $this->state([
            'status'       => OrderStatus::Cancelled,
            'cancelled_at' => now(),
        ]);
    }
}
