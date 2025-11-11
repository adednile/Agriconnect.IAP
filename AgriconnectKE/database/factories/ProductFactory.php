<?php
// database/factories/ProductFactory.php
namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            'farmer_id' => User::factory()->farmer(),
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->paragraph(),
            'price' => $this->faker->randomFloat(2, 50, 1000),
            'quantity' => $this->faker->numberBetween(1, 200),
            'category' => $this->faker->randomElement(['vegetables', 'fruits', 'grains', 'dairy', 'poultry']),
            'is_available' => true,
            'accepts_bids' => $this->faker->boolean(),
            'image' => null,
        ];
    }

    public function unavailable(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_available' => false,
        ]);
    }

    public function withBids(): static
    {
        return $this->state(fn (array $attributes) => [
            'accepts_bids' => true,
        ]);
    }

    public function withoutBids(): static
    {
        return $this->state(fn (array $attributes) => [
            'accepts_bids' => false,
        ]);
    }
}