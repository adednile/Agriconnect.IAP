<?php
// database/factories/BidFactory.php
namespace Database\Factories;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BidFactory extends Factory
{
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'buyer_id' => User::factory()->buyer(),
            'amount' => $this->faker->randomFloat(2, 10, 500),
            'status' => 'pending',
        ];
    }

    public function accepted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'accepted',
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'rejected',
        ]);
    }
}