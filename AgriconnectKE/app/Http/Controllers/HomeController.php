<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\User;
use App\Models\Order;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Get featured products (latest 4 products)
        $featuredProducts = Product::with('farmer')
            ->latest()
            ->take(4)
            ->get();

        // Get platform statistics
        $stats = [
            'farmers' => User::where('role', 'farmer')->count(),
            'buyers' => User::where('role', 'buyer')->count(),
            'products' => Product::count(),
            'deliveries' => Order::where('status', 'delivered')->count(),
        ];

        // Sample testimonials (you may want to create a Testimonial model later)
        $testimonials = [
            (object)[
                'content' => "AgriconnectKE has transformed how I sell my produce. Direct connection with buyers means better prices for my crops.",
                'user' => (object)[
                    'name' => 'John Mwangi',
                    'role' => 'farmer',
                    'avatar' => '/images/testimonials/farmer1.jpg'
                ]
            ],
            (object)[
                'content' => "I love the transparency and real-time tracking. I always know exactly when my orders will arrive.",
                'user' => (object)[
                    'name' => 'Sarah Kamau',
                    'role' => 'buyer',
                    'avatar' => '/images/testimonials/buyer1.jpg'
                ]
            ],
            (object)[
                'content' => "The platform has made my delivery work more efficient. The GPS tracking and route optimization are fantastic!",
                'user' => (object)[
                    'name' => 'David Omondi',
                    'role' => 'driver',
                    'avatar' => '/images/testimonials/driver1.jpg'
                ]
            ]
        ];

        return view('home', compact('featuredProducts', 'stats', 'testimonials'));
    }
}