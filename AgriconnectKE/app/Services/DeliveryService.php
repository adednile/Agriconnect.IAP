<?php
// app/Services/DeliveryService.php
namespace App\Services;

use App\Models\User;
use App\Models\Order;
use App\Models\Notification;

class DeliveryService
{
    // Calculate delivery cost based on distance
    public function calculateDeliveryCost($buyer, $farmer)
    {
        if (!$buyer->latitude || !$buyer->longitude || !$farmer->latitude || !$farmer->longitude) {
            return 200; // Default cost if coordinates not available
        }

        $distance = $this->calculateDistance(
            $farmer->latitude, $farmer->longitude,
            $buyer->latitude, $buyer->longitude
        );

        // Base cost + distance rate (Ksh per km)
        $baseCost = 100;
        $ratePerKm = 50;

        return $baseCost + ($distance * $ratePerKm);
    }

    // Calculate distance between two points using Haversine formula
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371; // kilometers

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat/2) * sin($dLat/2) + 
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * 
             sin($dLon/2) * sin($dLon/2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        
        return $earthRadius * $c;
    }

    // Assign the nearest available driver to an order
    public function assignDriver(Order $order)
    {
        $availableDrivers = User::availableDrivers()->with('driverLocation')->get();
        
        if ($availableDrivers->isEmpty()) {
            return null; // No available drivers
        }

        $nearestDriver = null;
        $shortestDistance = PHP_FLOAT_MAX;

        foreach ($availableDrivers as $driver) {
            if (!$driver->driverLocation) continue;

            $distance = $this->calculateDistance(
                $driver->driverLocation->latitude,
                $driver->driverLocation->longitude,
                $order->delivery_lat,
                $order->delivery_lng
            );

            if ($distance < $shortestDistance) {
                $shortestDistance = $distance;
                $nearestDriver = $driver;
            }
        }

        if ($nearestDriver) {
            $order->update(['driver_id' => $nearestDriver->id]);

            // Notify driver
            Notification::create([
                'user_id' => $nearestDriver->id,
                'title' => 'New Delivery Assignment',
                'message' => "You have been assigned to deliver order #{$order->id}. Distance: " . round($shortestDistance, 2) . "km",
                'type' => 'info'
            ]);

            return $nearestDriver;
        }

        return null;
    }

    // Get route information for delivery
    public function getRouteInfo($startLat, $startLng, $endLat, $endLng)
    {
        // In a real application, this would call a routing API like OSRM or Google Maps
        $distance = $this->calculateDistance($startLat, $startLng, $endLat, $endLng);
        $estimatedTime = $distance * 2; // Rough estimate: 2 minutes per km

        return [
            'distance' => round($distance, 2),
            'estimated_time' => round($estimatedTime),
            'route_coordinates' => $this->generateRouteCoordinates($startLat, $startLng, $endLat, $endLng)
        ];
    }

    // Generate simplified route coordinates (in real app, use routing API)
    private function generateRouteCoordinates($startLat, $startLng, $endLat, $endLng)
    {
        $steps = 10;
        $route = [];

        for ($i = 0; $i <= $steps; $i++) {
            $ratio = $i / $steps;
            $lat = $startLat + ($endLat - $startLat) * $ratio;
            $lng = $startLng + ($endLng - $startLng) * $ratio;
            
            $route[] = [$lat, $lng];
        }

        return $route;
    }
}