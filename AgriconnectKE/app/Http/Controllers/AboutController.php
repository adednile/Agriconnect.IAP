<?php

namespace App\Http\Controllers;
use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use Illuminate\Http\Request;

class AboutController extends Controller
{
    /**
     * Show the about page.
     */
    public function index()
    {
        // Get platform statistics
        $stats = [
            'farmers' => User::where('role', 'farmer')->count(),
            'buyers' => User::where('role', 'buyer')->count(),
            'products' => Product::count(),
            'deliveries' => Order::where('status', 'delivered')->count(),
        ];

        return view('about', compact('stats'));
    }
}