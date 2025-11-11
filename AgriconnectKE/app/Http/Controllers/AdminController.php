<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Product;
use App\Models\Order;

class AdminController extends Controller
{
    public function dashboard()
    {
        $stats = [
            'total_users' => User::count(),
            'total_farmers' => User::where('role', 'farmer')->count(),
            'total_buyers' => User::where('role', 'buyer')->count(),
            'total_drivers' => User::where('role', 'driver')->count(),
            'total_products' => Product::count(),
            'total_orders' => Order::count(),
        ];

        return view('admin.dashboard', compact('stats'));
    }

    public function users()
    {
        $users = User::latest()->get();
        return view('admin.users', compact('users'));
    }

    public function products()
    {
        return view('admin.products');
    }

    public function orders()
    {
        return view('admin.orders');
    }

    public function trackDrivers()
    {
        $drivers = User::where('role', 'driver')
            ->with('driverLocation')
            ->get();
            
        return view('admin.track-drivers', compact('drivers'));
    }

    public function systemStats()
    {
        return view('admin.system-stats');
    }
}