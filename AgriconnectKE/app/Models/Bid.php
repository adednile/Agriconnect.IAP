<?php
// app/Models/Bid.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bid extends Model
{
    use HasFactory;

    protected $fillable = ['product_id', 'buyer_id', 'amount', 'status'];

    protected $casts = [
        'amount' => 'decimal:2'
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

    public function order()
    {
        return $this->hasOne(Order::class);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeAccepted($query)
    {
        return $query->where('status', 'accepted');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopeForProduct($query, $productId)
    {
        return $query->where('product_id', $productId);
    }

    // Check if bid can be accepted
    public function canBeAccepted()
    {
        return $this->status === 'pending' && $this->product->is_available;
    }

    // Check if bid has been processed (has an order)
    public function hasBeenProcessed()
    {
        return !is_null($this->order);
    }

    // Accept the bid and create order
    public function accept()
    {
        if (!$this->canBeAccepted()) {
            return false;
        }

        $this->update(['status' => 'accepted']);
        return true;
    }

    // Reject the bid
    public function reject()
    {
        $this->update(['status' => 'rejected']);
        return true;
    }
}