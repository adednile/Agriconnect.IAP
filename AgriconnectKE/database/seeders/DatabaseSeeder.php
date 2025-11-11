<?php
// database/seeders/DatabaseSeeder.php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Product;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Create admin user
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@agriconnectke.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'phone' => '254700000000',
            'address' => 'Nairobi, Kenya',
            'latitude' => -1.2921,
            'longitude' => 36.8219,
        ]);

        // Create sample farmer
        $farmer = User::create([
            'name' => 'John Farmer',
            'email' => 'farmer@example.com',
            'password' => Hash::make('password'),
            'role' => 'farmer',
            'phone' => '254711111111',
            'address' => 'Kiambu, Kenya',
            'latitude' => -1.1714,
            'longitude' => 36.8357,
        ]);

        // Create sample buyer
        $buyer = User::create([
            'name' => 'Sarah Buyer',
            'email' => 'buyer@example.com',
            'password' => Hash::make('password'),
            'role' => 'buyer',
            'phone' => '254722222222',
            'address' => 'Westlands, Nairobi, Kenya',
            'latitude' => -1.2684,
            'longitude' => 36.8025,
        ]);

        // Create sample driver
        User::create([
            'name' => 'David Driver',
            'email' => 'driver@example.com',
            'password' => Hash::make('password'),
            'role' => 'driver',
            'phone' => '254733333333',
            'address' => 'Nairobi, Kenya',
            'latitude' => -1.2921,
            'longitude' => 36.8219,
            'is_available' => true,
        ]);

        // Create sample products
        Product::create([
            'farmer_id' => $farmer->id,
            'name' => 'Fresh Organic Tomatoes',
            'description' => 'Freshly harvested organic tomatoes from our farm in Kiambu. Grown without pesticides and chemicals.',
            'price' => 150.00,
            'quantity' => 100,
            'category' => 'vegetables',
            'is_available' => true,
            'accepts_bids' => true,
        ]);

        Product::create([
            'farmer_id' => $farmer->id,
            'name' => 'Premium Avocados',
            'description' => 'Premium Hass avocados, ripe and ready to eat. Perfect for salads and guacamole.',
            'price' => 80.00,
            'quantity' => 50,
            'category' => 'fruits',
            'is_available' => true,
            'accepts_bids' => false,
        ]);

        Product::create([
            'farmer_id' => $farmer->id,
            'name' => 'Organic Kale',
            'description' => 'Fresh organic kale, packed with nutrients. Perfect for smoothies and salads.',
            'price' => 120.00,
            'quantity' => 30,
            'category' => 'vegetables',
            'is_available' => true,
            'accepts_bids' => true,
        ]);
    }
}