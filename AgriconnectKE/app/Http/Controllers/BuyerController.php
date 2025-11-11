<?php
namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Bid;
use App\Models\Order;
use App\Models\Notification;
use App\Services\DeliveryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BuyerController extends Controller
{
    
    public function dashboard()
    {
        $buyerId = Auth::id();
        
        $orders = Order::where('buyer_id', $buyerId)
            ->with('product')
            ->latest()
            ->get();
            
        $bids = Bid::where('buyer_id', $buyerId)
            ->with('product')
            ->latest()
            ->get();
            
        $products = Product::available()->latest()->take(6)->get();

        return view('buyer.dashboard', compact('orders', 'bids', 'products'));
    }

    // Add these methods to your existing BuyerController

public function cart()
{
    $cart = session()->get('cart', []);
    $cartItems = [];
    $total = 0;

    foreach ($cart as $productId => $item) {
        // FIX: Load product with farmer relationship
        $product = Product::with('farmer')->find($productId);
        if ($product) {
            $itemTotal = $product->price * $item['quantity'];
            $total += $itemTotal;
            
            $cartItems[] = [
                'product' => $product,
                'quantity' => $item['quantity'],
                'item_total' => $itemTotal
            ];
        }
    }

    return view('buyer.cart', compact('cartItems', 'total'));
}

public function addToCart(Request $request, Product $product)
{
    try {
        // Check if user is authenticated and is a buyer
        if (!auth()->check() || auth()->user()->role !== 'buyer') {
            return response()->json([
                'success' => false,
                'error' => 'Please log in as a buyer to add items to cart'
            ], 401);
        }

        // Check product availability
        if (!$product->is_available) {
            return response()->json([
                'success' => false,
                'error' => 'This product is currently unavailable'
            ], 400);
        }

        if ($product->quantity <= 0) {
            return response()->json([
                'success' => false,
                'error' => 'Product is out of stock'
            ], 400);
        }

        $quantity = $request->input('quantity', 1);
        
        // Validate quantity
        if ($quantity < 1) {
            return response()->json([
                'success' => false,
                'error' => 'Quantity must be at least 1'
            ], 400);
        }

        if ($quantity > $product->quantity) {
            return response()->json([
                'success' => false,
                'error' => "Only {$product->quantity} units available"
            ], 400);
        }

        // Get current cart from session
        $cart = session()->get('cart', []);

        // Check if product already in cart
        if (isset($cart[$product->id])) {
            $newQuantity = $cart[$product->id]['quantity'] + $quantity;
            
            if ($newQuantity > $product->quantity) {
                return response()->json([
                    'success' => false,
                    'error' => "Cannot add more than available stock. You already have {$cart[$product->id]['quantity']} in cart."
                ], 400);
            }
            
            $cart[$product->id]['quantity'] = $newQuantity;
        } else {
            $cart[$product->id] = [
                'product_id' => $product->id,
                'quantity' => $quantity,
                'added_at' => now()->toDateTimeString(),
                'product_name' => $product->name,
                'product_price' => $product->price,
                'product_image' => $product->image
            ];
        }

        // Save cart to session
        session()->put('cart', $cart);

        // Calculate total cart count
        $cartCount = array_sum(array_column($cart, 'quantity'));

        return response()->json([
            'success' => true,
            'message' => "{$product->name} added to cart successfully!",
            'cart_count' => $cartCount,
            'product_name' => $product->name
        ]);

    } catch (\Exception $e) {
        \Log::error('Add to cart error: ' . $e->getMessage());
        
        return response()->json([
            'success' => false,
            'error' => 'Failed to add product to cart. Please try again.'
        ], 500);
    }
}
public function updateCart(Request $request, Product $product)
{
    $validated = $request->validate([
        'quantity' => 'required|integer|min:1|max:' . $product->quantity
    ]);

    $cart = session()->get('cart', []);
    $quantity = $validated['quantity'];

    if ($quantity <= 0) {
        unset($cart[$product->id]);
    } else {
        $cart[$product->id]['quantity'] = $quantity;
    }

    session()->put('cart', $cart);

    return redirect()->route('buyer.cart')->with('success', 'Cart updated successfully!');
}
public function removeFromCart(Product $product)
{
    $cart = session()->get('cart', []);

    if (isset($cart[$product->id])) {
        unset($cart[$product->id]);
        session()->put('cart', $cart);
    }

    return redirect()->route('buyer.cart')->with('success', 'Product removed from cart successfully!');
}

public function clearCart()
{
    session()->forget('cart');
    return redirect()->route('buyer.cart')->with('success', 'Cart cleared successfully!');
}

// Helper method to get cart count
private function getCartCount()
{
    $cart = session()->get('cart', []);
    return array_sum(array_column($cart, 'quantity'));
}

    public function market()
    {
        $products = Product::available()
            ->with('farmer')
            ->latest()
            ->paginate(12);

        return view('buyer.market', compact('products'));
    }

    public function orders()
    {
        $orders = Order::where('buyer_id', Auth::id())
            ->with(['product', 'farmer'])
            ->latest()
            ->paginate(10);

        return view('buyer.orders', compact('orders'));
    }

    public function bids()
    {
        $bids = Bid::where('buyer_id', Auth::id())
            ->with(['product', 'product.farmer'])
            ->latest()
            ->paginate(10);

        return view('buyer.bids', compact('bids'));
    }

    public function placeBid(Request $request, Product $product)
    {
        if (!$product->accepts_bids) {
            return back()->with('error', 'This product does not accept bids.');
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01'
        ]);

        $bid = Bid::create([
            'product_id' => $product->id,
            'buyer_id' => Auth::id(),
            'amount' => $validated['amount'],
            'status' => 'pending'
        ]);

        // Notify farmer
        Notification::create([
            'user_id' => $product->farmer_id,
            'title' => 'New Bid Received',
            'message' => "You have received a new bid of Ksh " . number_format($validated['amount'], 2) . " for {$product->name}",
            'type' => 'info'
        ]);

        return redirect()->route('buyer.bids')
            ->with('success', 'Bid placed successfully!');
    }

public function purchase(Request $request, Product $product)
{
    $validated = $request->validate([
        'quantity' => 'required|integer|min:1|max:' . $product->quantity
    ]);

    $deliveryService = new DeliveryService();
    $buyer = Auth::user();
    $farmer = $product->farmer;

    $deliveryCost = $deliveryService->calculateDeliveryCost($buyer, $farmer);
    $subtotal = $product->price * $validated['quantity'];
    $totalAmount = $subtotal + $deliveryCost;

    // Create order
    $order = Order::create([
        'product_id' => $product->id,
        'buyer_id' => $buyer->id,
        'farmer_id' => $farmer->id,
        'amount' => $totalAmount,
        'quantity' => $validated['quantity'],
        'status' => 'pending',
        'delivery_cost' => $deliveryCost,
        'delivery_address' => $buyer->address,
        'delivery_lat' => $buyer->latitude ?? -1.2921,
        'delivery_lng' => $buyer->longitude ?? 36.8219,
    ]);

    // Update product quantity
    $product->decrement('quantity', $validated['quantity']);

    if ($product->quantity <= 0) {
        $product->update(['is_available' => false]);
    }

    // FIXED: Use the correct route name
    return redirect()->route('buyer.checkout', $order)
        ->with('success', 'Order created successfully! Proceed to checkout.');
}

public function checkoutCart()
{
    $cart = session()->get('cart', []);
    
    if (empty($cart)) {
        return redirect()->route('buyer.cart')->with('error', 'Your cart is empty.');
    }

    $cartItems = [];
    $subtotal = 0;
    $deliveryService = new DeliveryService();
    $buyer = Auth::user();

    foreach ($cart as $productId => $item) {
        // FIX: Load product with farmer relationship
        $product = Product::with('farmer')->find($productId);
        if ($product && $product->is_available && $product->quantity >= $item['quantity']) {
            $itemTotal = $product->price * $item['quantity'];
            $subtotal += $itemTotal;
            
            $cartItems[] = [
                'product' => $product,
                'quantity' => $item['quantity'],
                'item_total' => $itemTotal
            ];
        }
    }

    if (empty($cartItems)) {
        return redirect()->route('buyer.cart')->with('error', 'No valid products in your cart.');
    }

    // Calculate delivery cost (using first product's farmer as reference)
    $firstProduct = $cartItems[0]['product'];
    $deliveryCost = $deliveryService->calculateDeliveryCost($buyer, $firstProduct->farmer);
    
    $total = $subtotal + $deliveryCost;

    return view('buyer.checkout-cart', compact('cartItems', 'subtotal', 'deliveryCost', 'total'));
}

// Temporary debugging method - add this to BuyerController
public function debugBidOrder($orderId)
{
    $order = Order::with(['product', 'farmer', 'bid'])->find($orderId);
    
    if (!$order) {
        return response()->json(['error' => 'Order not found'], 404);
    }
    
    return response()->json([
        'order' => [
            'id' => $order->id,
            'status' => $order->status,
            'bid_id' => $order->bid_id,
            'buyer_id' => $order->buyer_id,
            'current_user_id' => Auth::id(),
            'amount' => $order->amount,
            'product_available' => $order->product->is_available,
            'product_quantity' => $order->product->quantity,
        ],
        'checkout_url' => route('buyer.checkout.bid', $order),
        'is_authorized' => $order->buyer_id === Auth::id(),
    ]);
}

public function processPayment(Request $request, Order $order)
{
    if ($order->buyer_id !== Auth::id()) {
        abort(403, 'Unauthorized action.');
    }

    // Check if order is still pending
    if ($order->status !== 'pending') {
        return redirect()->route('buyer.orders')
            ->with('error', 'This order has already been processed.');
    }

    $validated = $request->validate([
        'phone' => 'required|string',
        'terms' => 'required|accepted'
    ]);

    // Simulate M-Pesa payment
    $mpesaReceipt = 'MPE' . time() . rand(1000, 9999);

    // COMPLETE FIX: Update with all timestamp columns
    $order->update([
        'status' => 'paid',
        'mpesa_receipt' => $mpesaReceipt,
        'paid_at' => now()
    ]);

    // Assign driver
    $deliveryService = new DeliveryService();
    $deliveryService->assignDriver($order);

    // Notify buyer
    Notification::create([
        'user_id' => Auth::id(),
        'title' => 'Payment Successful',
        'message' => "Your payment of Ksh " . number_format($order->amount, 2) . " for order #{$order->id} was successful. Receipt: {$mpesaReceipt}",
        'type' => 'success'
    ]);

    // Notify farmer
    Notification::create([
        'user_id' => $order->farmer_id,
        'title' => 'Order Paid',
        'message' => "Order #{$order->id} has been paid. Amount: Ksh " . number_format($order->amount, 2),
        'type' => 'success'
    ]);

    return redirect()->route('buyer.orders')
        ->with('success', 'Payment processed successfully! Your order is being processed.');
}  // Add this method to BuyerController

public function downloadReceipt(Order $order)
{
    if ($order->buyer_id !== Auth::id()) {
        abort(403, 'Unauthorized action.');
    }

    if ($order->status !== 'paid' && $order->status !== 'delivered') {
        return redirect()->route('buyer.track-order', $order)
            ->with('error', 'Receipt is only available for paid or delivered orders.');
    }

    // Generate receipt content (you can create a PDF later)
    $receiptContent = $this->generateReceiptContent($order);

    // For now, we'll create a simple text receipt
    // You can upgrade to PDF using libraries like DomPDF or TCPDF later
    $filename = "receipt-order-{$order->id}.txt";

    return response()->streamDownload(function () use ($receiptContent) {
        echo $receiptContent;
    }, $filename, [
        'Content-Type' => 'text/plain',
    ]);
}

private function generateReceiptContent(Order $order)
{
    $content = "=================================\n";
    $content .= "        AGRICONNECT KE\n";
    $content .= "          OFFICIAL RECEIPT\n";
    $content .= "=================================\n\n";
    
    $content .= "Receipt No: {$order->mpesa_receipt}\n";
    $content .= "Order ID: #{$order->id}\n";
    $content .= "Date: {$order->paid_at->format('F d, Y H:i')}\n\n";
    
    $content .= "---------------------------------\n";
    $content .= "PRODUCT DETAILS\n";
    $content .= "---------------------------------\n";
    $content .= "Product: {$order->product->name}\n";
    $content .= "Farmer: {$order->farmer->name}\n";
    $content .= "Quantity: {$order->quantity}\n";
    $content .= "Unit Price: Ksh " . number_format($order->product->price, 2) . "\n\n";
    
    $content .= "---------------------------------\n";
    $content .= "PAYMENT SUMMARY\n";
    $content .= "---------------------------------\n";
    $content .= "Product Subtotal: Ksh " . number_format($order->product->price * $order->quantity, 2) . "\n";
    $content .= "Delivery Cost: Ksh " . number_format($order->delivery_cost, 2) . "\n";
    $content .= "Total Amount: Ksh " . number_format($order->amount, 2) . "\n\n";
    
    $content .= "---------------------------------\n";
    $content .= "DELIVERY INFORMATION\n";
    $content .= "---------------------------------\n";
    $content .= "Delivery Address: {$order->delivery_address}\n";
    $content .= "Order Status: " . ucfirst($order->status) . "\n";
    
    if ($order->driver) {
        $content .= "Driver: {$order->driver->name}\n";
        $content .= "Driver Phone: {$order->driver->phone}\n";
    }
    
    $content .= "\n---------------------------------\n";
    $content .= "THANK YOU FOR YOUR ORDER!\n";
    $content .= "=================================\n";
    
    return $content;
}
public function quickView(Product $product)
{
    $product->load('farmer');
    
    $similarProducts = Product::available()
        ->where('category', $product->category)
        ->where('id', '!=', $product->id)
        ->with('farmer')
        ->take(4)
        ->get();

    return response()->json([
        'success' => true,
        'product' => [
            'id' => $product->id,
            'name' => $product->name,
            'description' => $product->description,
            'price' => $product->price,
            'quantity' => $product->quantity,
            'category' => $product->category,
            'image_url' => $product->image ? asset('storage/' . $product->image) : asset('images/default-product.jpg'),
            'farmer' => [
                'name' => $product->farmer->name,
                'phone' => $product->farmer->phone,
                'address' => $product->farmer->address,
            ],
            'is_available' => $product->is_available,
            'accepts_bids' => $product->accepts_bids,
        ],
        'similar_products' => $similarProducts->map(function($similar) {
            return [
                'id' => $similar->id,
                'name' => $similar->name,
                'price' => $similar->price,
                'image_url' => $similar->image ? asset('storage/' . $similar->image) : asset('images/default-product.jpg'),
                'farmer_name' => $similar->farmer->name,
            ];
        }),
        'html' => view('partials.quick-view-content', compact('product', 'similarProducts'))->render()
    ]);
}
    // Add this method to handle checkout for bid orders
// Add this method to your BuyerController
// Add this method to your BuyerController
// In BuyerController - make sure this method exists and is correct
public function checkoutBidOrder(Order $order)
{
    \Log::info('Checkout bid order accessed', [
        'order_id' => $order->id,
        'buyer_id' => Auth::id(),
        'order_buyer_id' => $order->buyer_id,
        'order_status' => $order->status,
        'bid_id' => $order->bid_id
    ]);

    // Authorization check
    if ($order->buyer_id !== Auth::id()) {
        \Log::warning('Unauthorized access to bid order', [
            'order_id' => $order->id,
            'user_id' => Auth::id()
        ]);
        abort(403, 'Unauthorized action. This order does not belong to you.');
    }

    // Status validation
    if ($order->status !== 'pending') {
        \Log::warning('Attempt to checkout non-pending bid order', [
            'order_id' => $order->id,
            'status' => $order->status
        ]);
        return redirect()->route('buyer.orders')
            ->with('error', 'This order has already been processed.');
    }

    // Check if this is a bid order
    if (!$order->bid_id) {
        \Log::warning('Attempt to checkout non-bid order as bid order', [
            'order_id' => $order->id
        ]);
        return redirect()->route('buyer.checkout', $order)
            ->with('error', 'This is not a bid order.');
    }

    // Load relationships
    $order->load(['product.farmer', 'bid']);

    if (!$order->bid) {
        \Log::error('Bid not found for bid order', [
            'order_id' => $order->id,
            'bid_id' => $order->bid_id
        ]);
        return redirect()->route('buyer.orders')
            ->with('error', 'Bid information not found for this order.');
    }

    // Ensure delivery cost is calculated
    if (!$order->delivery_cost || $order->delivery_cost == 0) {
        $deliveryService = new DeliveryService();
        $buyer = Auth::user();
        $deliveryCost = $deliveryService->calculateDeliveryCost($buyer, $order->product->farmer);
        
        $totalAmount = $order->bid->amount + $deliveryCost;
        
        $order->update([
            'delivery_cost' => $deliveryCost,
            'amount' => $totalAmount
        ]);
        
        $order->refresh();
    }

    \Log::info('Rendering checkout bid view', ['order_id' => $order->id]);

    return view('buyer.checkout-bid', compact('order'));
}    public function processCartCheckout(Request $request)
{
    $cart = session()->get('cart', []);
    
    if (empty($cart)) {
        return redirect()->route('buyer.cart')->with('error', 'Your cart is empty.');
    }

    $validated = $request->validate([
        'phone' => 'required|string',
        'terms' => 'required|accepted'
    ]);

    $buyer = Auth::user();
    $orders = [];
    $failedProducts = [];

    foreach ($cart as $productId => $item) {
        // FIX: Load product with farmer relationship
        $product = Product::with('farmer')->find($productId);
        
        if (!$product || !$product->is_available || $product->quantity < $item['quantity']) {
            $failedProducts[] = $product ? $product->name : 'Unknown Product';
            continue;
        }

        $deliveryService = new DeliveryService();
        $deliveryCost = $deliveryService->calculateDeliveryCost($buyer, $product->farmer);
        $subtotal = $product->price * $item['quantity'];
        $totalAmount = $subtotal + $deliveryCost;

        // Simulate M-Pesa payment for each product
        $mpesaReceipt = 'MPE' . time() . rand(1000, 9999) . '_' . $productId;

        // Create order
        $order = Order::create([
            'product_id' => $product->id,
            'buyer_id' => $buyer->id,
            'farmer_id' => $product->farmer_id,
            'amount' => $totalAmount,
            'quantity' => $item['quantity'],
            'status' => 'paid',
            'mpesa_receipt' => $mpesaReceipt,
            'delivery_cost' => $deliveryCost,
            'delivery_address' => $buyer->address,
            'delivery_lat' => $buyer->latitude ?? -1.2921,
            'delivery_lng' => $buyer->longitude ?? 36.8219,
            'paid_at' => now()
        ]);

        // Update product quantity
        $product->decrement('quantity', $item['quantity']);

        // Mark product as unavailable if quantity reaches 0
        if ($product->quantity <= 0) {
            $product->update(['is_available' => false]);
        }

        // Assign driver
        $deliveryService->assignDriver($order);

        $orders[] = $order;

        // Notify buyer
        Notification::create([
            'user_id' => $buyer->id,
            'title' => 'Order Confirmed',
            'message' => "Your order for {$product->name} (Qty: {$item['quantity']}) has been confirmed. Receipt: {$mpesaReceipt}",
            'type' => 'success'
        ]);

        // Notify farmer
        Notification::create([
            'user_id' => $product->farmer_id,
            'title' => 'New Order',
            'message' => "You have a new order for {$product->name} (Qty: {$item['quantity']}). Total: Ksh " . number_format($totalAmount, 2),
            'type' => 'info'
        ]);
    }

    // Clear the cart after successful checkout
    session()->forget('cart');

    $message = 'Checkout completed successfully! ' . count($orders) . ' order(s) placed.';
    
    if (!empty($failedProducts)) {
        $message .= ' Failed to process: ' . implode(', ', $failedProducts);
    }

    return redirect()->route('buyer.orders')
        ->with('success', $message)
        ->with('orders', $orders);
}
    public function trackOrder(Order $order)
    {
        if ($order->buyer_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $order->load(['product', 'farmer', 'driver']);
        $driverLocation = $order->driver ? $order->driver->driverLocation : null;

        // Calculate progress percentage
        $progressPercentage = 0;
        switch ($order->status) {
            case 'paid': $progressPercentage = 50; break;
            case 'shipped': $progressPercentage = 75; break;
            case 'delivered': $progressPercentage = 100; break;
            default: $progressPercentage = 25;
        }

        return view('buyer.track-order', compact('order', 'driverLocation', 'progressPercentage'));
    }
}