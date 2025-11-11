<?php
// app/Models/Order.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id', 'buyer_id', 'farmer_id', 'driver_id', 'bid_id', 'amount',
        'quantity', 'status', 'mpesa_receipt', 'delivery_cost', 'delivery_address',
        'delivery_lat', 'delivery_lng', 'paid_at', 'shipped_at', 'delivered_at', 'cancelled_at'
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'amount' => 'decimal:2',
        'delivery_cost' => 'decimal:2'
    ];
    // Relationships
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function farmer()
    {
        return $this->belongsTo(User::class, 'farmer_id');
    }

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function bid()
    {
        return $this->belongsTo(Bid::class);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeShipped($query)
    {
        return $query->where('status', 'shipped');
    }

    public function scopeDelivered($query)
    {
        return $query->where('status', 'delivered');
    }

    public function scopeBidOrders($query)
    {
        return $query->whereNotNull('bid_id');
    }

    public function scopeRegularOrders($query)
    {
        return $query->whereNull('bid_id');
    }

    // Check if order can be shipped
    public function canBeShipped()
    {
        return $this->status === 'paid' && $this->driver_id !== null;
    }

    // Check if order can be delivered
    public function canBeDelivered()
    {
        return $this->status === 'shipped';
    }

    // Check if this is a bid order
    public function isBidOrder()
    {
        return !is_null($this->bid_id);
    }

    // Get the original bid amount (without delivery)
    public function getBidAmountAttribute()
    {
        if ($this->isBidOrder() && $this->bid) {
            return $this->bid->amount;
        }
        return $this->amount - $this->delivery_cost;
    }

    // Update order status with timestamps
    public function markAsPaid()
    {
        $this->update([
            'status' => 'paid',
            'paid_at' => now()
        ]);
    }

    public function markAsShipped()
    {
        $this->update([
            'status' => 'shipped', 
            'shipped_at' => now()
        ]);
    }

    public function markAsDelivered()
    {
        $this->update([
            'status' => 'delivered',
            'delivered_at' => now()
        ]);
    }
}