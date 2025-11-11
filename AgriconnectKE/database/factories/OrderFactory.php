<?php
// database/factories/OrderFactory.php
namespace Database\Factories;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    public function definition(): array
    {
        $product = Product::factory()->create();
        
        return [
            'product_id' => $product->id,
            'buyer_id' => User::factory()->buyer(),
            'farmer_id' => $product->farmer_id,
            'driver_id' => User::factory()->driver(),
            'amount' => $this->faker->randomFloat(2, 100, 2000),
            'quantity' => $this->faker->numberBetween(1, 10),
            'status' => $this->faker->randomElement(['pending', 'paid', 'shipped', 'delivered']),
            'mpesa_receipt' => 'MPE' . $this->faker->unique()->numerify('########'),
            'delivery_cost' => $this->faker->randomFloat(2, 50, 500),
            'delivery_address' => $this->faker->address(),
            'delivery_lat' => $this->faker->latitude(-4.0, -1.0),
            'delivery_lng' => $this->faker->longitude(34.0, 42.0),
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
        ]);
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'paid',
        ]);
    }

    public function shipped(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'shipped',
        ]);
    }

    public function delivered(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'delivered',
        ]);
    }
}