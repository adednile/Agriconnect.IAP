<?php
namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\DriverLocation;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DriverController extends Controller
{
    public function dashboard()
    {
        $driverId = Auth::id();
        
        $assignedDeliveries = Order::where('driver_id', $driverId)
            ->whereIn('status', ['paid', 'shipped'])
            ->count();
            
        $completedDeliveries = Order::where('driver_id', $driverId)
            ->where('status', 'delivered')
            ->count();
            
        $inProgressDeliveries = Order::where('driver_id', $driverId)
            ->where('status', 'shipped')
            ->count();
            
        $totalEarnings = Order::where('driver_id', $driverId)
            ->where('status', 'delivered')
            ->sum('delivery_cost');

        $currentDeliveries = Order::where('driver_id', $driverId)
            ->whereIn('status', ['paid', 'shipped'])
            ->with(['product', 'buyer', 'farmer'])
            ->latest()
            ->get();

        $recentDeliveries = Order::where('driver_id', $driverId)
            ->with('product')
            ->latest()
            ->take(5)
            ->get();

        $driverLocation = DriverLocation::where('driver_id', $driverId)->first();

        return view('driver.dashboard', compact(
            'assignedDeliveries',
            'completedDeliveries',
            'inProgressDeliveries',
            'totalEarnings',
            'currentDeliveries',
            'recentDeliveries',
            'driverLocation'
        ));
    }

    // Add this method to your DriverController
public function getCurrentLocation(Request $request)
{
    $driverId = $request->get('driver_id') ?? Auth::id();
    
    // If user is not a driver and no driver_id provided, check if they're admin or the buyer
    if (Auth::user()->role !== 'driver' && !$request->has('driver_id')) {
        return response()->json([
            'error' => 'Driver ID required'
        ], 400);
    }

    $driverLocation = DriverLocation::where('driver_id', $driverId)->first();

    if ($driverLocation) {
        return response()->json([
            'success' => true,
            'latitude' => $driverLocation->latitude,
            'longitude' => $driverLocation->longitude,
            'location_updated_at' => $driverLocation->location_updated_at->toISOString(),
            'driver_id' => $driverLocation->driver_id
        ]);
    }

    return response()->json([
        'success' => false,
        'error' => 'Location not found for driver'
    ], 404);
}
    public function deliveries()
    {
        $deliveries = Order::where('driver_id', Auth::id())
            ->with(['product', 'buyer', 'farmer'])
            ->latest()
            ->paginate(10);

        return view('driver.deliveries', compact('deliveries'));
    }

    public function showDelivery(Order $order)
    {
        if ($order->driver_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $order->load(['product', 'buyer', 'farmer']);
        return view('driver.delivery-show', compact('order'));
    }

    public function updateDeliveryStatus(Request $request, Order $order)
    {
        if ($order->driver_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'status' => 'required|in:shipped,delivered'
        ]);

        $oldStatus = $order->status;
        $order->update(['status' => $validated['status']]);

        // Notify buyer
        $statusMessage = $validated['status'] === 'shipped' ? 'has been shipped and is on the way' : 'has been delivered';
        
        Notification::create([
            'user_id' => $order->buyer_id,
            'title' => 'Order ' . ucfirst($validated['status']),
            'message' => "Your order #{$order->id} {$statusMessage}. Driver: " . Auth::user()->name,
            'type' => 'info'
        ]);

        // Notify farmer if delivered
        if ($validated['status'] === 'delivered') {
            Notification::create([
                'user_id' => $order->farmer_id,
                'title' => 'Order Delivered',
                'message' => "Order #{$order->id} has been successfully delivered to the buyer.",
                'type' => 'success'
            ]);
        }

        return redirect()->back()->with('success', "Order status updated to {$validated['status']} successfully.");
    }

    public function updateLocation(Request $request)
    {
        $validated = $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric'
        ]);

        $driverId = Auth::id();

        DriverLocation::updateOrCreate(
            ['driver_id' => $driverId],
            [
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
                'location_updated_at' => now()
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Location updated successfully'
        ]);
    }

    public function toggleAvailability()
    {
        $driver = Auth::user();
        $driver->update([
            'is_available' => !$driver->is_available
        ]);

        $status = $driver->is_available ? 'online' : 'offline';
        return redirect()->back()->with('success', "You are now {$status}.");
    }
}