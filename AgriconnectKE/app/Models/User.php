<?php
// app/Models/User.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'role', 'phone', 'address', 
        'latitude', 'longitude', 'is_available'
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_available' => 'boolean',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8'
    ];

    // Relationships
    public function products()
    {
        return $this->hasMany(Product::class, 'farmer_id');
    }

    public function bids()
    {
        return $this->hasMany(Bid::class, 'buyer_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'buyer_id');
    }

    public function farmerOrders()
    {
        return $this->hasMany(Order::class, 'farmer_id');
    }

    public function driverOrders()
    {
        return $this->hasMany(Order::class, 'driver_id');
    }

    public function driverLocation()
    {
        return $this->hasOne(DriverLocation::class, 'driver_id');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    // Bid-related relationships
    public function pendingBids()
    {
        return $this->bids()->where('status', 'pending');
    }

    public function acceptedBids()
    {
        return $this->bids()->where('status', 'accepted');
    }

    public function bidOrders()
    {
        return $this->orders()->whereNotNull('bid_id');
    }

    // Scopes
    public function scopeFarmers($query)
    {
        return $query->where('role', 'farmer');
    }

    public function scopeBuyers($query)
    {
        return $query->where('role', 'buyer');
    }

    public function scopeDrivers($query)
    {
        return $query->where('role', 'driver');
    }

    public function scopeAvailableDrivers($query)
    {
        return $query->where('role', 'driver')->where('is_available', true);
    }

    public function scopeAdmins($query)
    {
        return $query->where('role', 'admin');
    }

    // Helper methods
    public function isFarmer()
    {
        return $this->role === 'farmer';
    }

    public function isBuyer()
    {
        return $this->role === 'buyer';
    }

    public function isDriver()
    {
        return $this->role === 'driver';
    }

    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    // Bid management methods
    public function hasPendingBids()
    {
        return $this->bids()->where('status', 'pending')->exists();
    }

    public function getPendingBidsCount()
    {
        return $this->bids()->where('status', 'pending')->count();
    }

    public function getAcceptedBids()
    {
        return $this->bids()->where('status', 'accepted')->with('order')->get();
    }

    // Order management methods
    public function getPendingBidOrders()
    {
        return $this->orders()
            ->whereNotNull('bid_id')
            ->where('status', 'pending')
            ->with(['product', 'bid'])
            ->get();
    }

    // Calculate distance between two coordinates (Haversine formula)
    public function calculateDistance($lat2, $lon2)
    {
        $lat1 = $this->latitude;
        $lon1 = $this->longitude;

        if (!$lat1 || !$lon1) return null;

        $earthRadius = 6371; // kilometers

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat/2) * sin($dLat/2) + 
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * 
             sin($dLon/2) * sin($dLon/2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        
        return $earthRadius * $c;
    }

    // Check if user can place bid on product
    public function canPlaceBid(Product $product)
    {
        return $this->isBuyer() && 
               $product->acceptsBids() && 
               !$this->bids()->where('product_id', $product->id)->where('status', 'pending')->exists();
    }
}