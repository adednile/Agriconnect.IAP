<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'farmer_id', 'name', 'description', 'image', 'price', 'quantity',
        'category', 'is_available', 'accepts_bids'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_available' => 'boolean',
        'accepts_bids' => 'boolean'
    ];

    // Relationships
    public function farmer()
    {
        return $this->belongsTo(User::class, 'farmer_id');
    }

    public function bids()
    {
        return $this->hasMany(Bid::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function pendingBids()
    {
        return $this->bids()->where('status', 'pending');
    }

    public function acceptedBids()
    {
        return $this->bids()->where('status', 'accepted');
    }

    // Scopes
    public function scopeAvailable($query)
    {
        return $query->where('is_available', true)->where('quantity', '>', 0);
    }

    public function scopeWithBids($query)
    {
        return $query->where('accepts_bids', true);
    }

    // Accessor for image URL
    public function getImageUrlAttribute()
    {
        if ($this->image) {
            return asset('storage/' . $this->image);
        }
        return asset('images/default-product.jpg');
    }

    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['search'] ?? null, function ($query, $search) {
            return $query->where(function ($query) use ($search) {
                $query->where('name', 'like', '%'.$search.'%')
                      ->orWhere('description', 'like', '%'.$search.'%');
            });
        })->when($filters['category'] ?? null, function ($query, $category) {
            return $query->where('category', $category);
        });
    }

    // Check if product can be purchased
    public function canBePurchased($quantity = 1)
    {
        return $this->is_available && $this->quantity >= $quantity;
    }

    // Check if product accepts bids
    public function acceptsBids()
    {
        return $this->accepts_bids && $this->is_available;
    }

    // Get the highest bid
    public function getHighestBid()
    {
        return $this->bids()->orderBy('amount', 'desc')->first();
    }

    // Get pending bids count
    public function getPendingBidsCountAttribute()
    {
        return $this->bids()->where('status', 'pending')->count();
    }

    // Update availability based on quantity
    public function updateAvailability()
    {
        $this->update([
            'is_available' => $this->quantity > 0
        ]);
    }

    // Reserve quantity for a bid (when accepted)
    public function reserveForBid($quantity = 1)
    {
        if ($this->quantity >= $quantity) {
            $this->decrement('quantity', $quantity);
            $this->updateAvailability();
            return true;
        }
        return false;
    }
}