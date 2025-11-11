<?php
use App\Http\Controllers\AboutController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BuyerController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\FarmerController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

// Public Routes
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');
Route::get('/search', [ProductController::class, 'search'])->name('products.search');
Route::get('/products/category/{category}', [ProductController::class, 'byCategory'])->name('products.byCategory');
Route::get('/about', [AboutController::class, 'index'])->name('about');

// Contact Routes
Route::get('/contact', [ContactController::class, 'index'])->name('contact');
Route::post('/contact', [ContactController::class, 'submit'])->name('contact.submit');

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected Routes
Route::middleware(['auth'])->group(function () {
    
    // Notification Routes
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::post('/{notification}/read', [NotificationController::class, 'markAsRead'])->name('read');
        Route::post('/read-all', [NotificationController::class, 'markAllAsRead'])->name('read-all');
        Route::delete('/{notification}', [NotificationController::class, 'destroy'])->name('destroy');
        Route::post('/clear-all', [NotificationController::class, 'clearAll'])->name('clear-all');
        
        // API routes for real-time notifications
        Route::get('/unread-count', [NotificationController::class, 'getUnreadCount'])->name('unread-count');
        Route::get('/recent', [NotificationController::class, 'getRecentNotifications'])->name('recent');
    });

    // Admin Routes
    Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/users', [AdminController::class, 'users'])->name('users');
        Route::get('/products', [AdminController::class, 'products'])->name('products');
        Route::get('/orders', [AdminController::class, 'orders'])->name('orders');
        Route::get('/track-drivers', [AdminController::class, 'trackDrivers'])->name('track-drivers');
        Route::get('/system-stats', [AdminController::class, 'systemStats'])->name('system-stats');
    });

    // Farmer Routes
    Route::middleware(['role:farmer'])->prefix('farmer')->name('farmer.')->group(function () {
        Route::get('/dashboard', [FarmerController::class, 'dashboard'])->name('dashboard');
        
        // Product management
        Route::get('/products', [FarmerController::class, 'products'])->name('products');
        Route::get('/products/create', [FarmerController::class, 'createProduct'])->name('products.create');
        Route::post('/products', [FarmerController::class, 'storeProduct'])->name('products.store');
        Route::get('/products/{product}/edit', [FarmerController::class, 'editProduct'])->name('products.edit');
        Route::put('/products/{product}', [FarmerController::class, 'updateProduct'])->name('products.update');
        Route::delete('/products/{product}', [FarmerController::class, 'destroyProduct'])->name('products.destroy');
        
        // Bid management
        Route::get('/bids', [FarmerController::class, 'bids'])->name('bids');
        Route::post('/bids/{bid}/accept', [FarmerController::class, 'acceptBid'])->name('bids.accept');
        Route::post('/bids/{bid}/reject', [FarmerController::class, 'rejectBid'])->name('bids.reject');

        // Sales management
        Route::get('/sales', [FarmerController::class, 'sales'])->name('sales');
        Route::get('/sales/{order}', [FarmerController::class, 'showSale'])->name('sales.show');
    });

    // Buyer Routes  
    Route::middleware(['role:buyer'])->prefix('buyer')->name('buyer.')->group(function () {
        // Dashboard and listings
        Route::get('/dashboard', [BuyerController::class, 'dashboard'])->name('dashboard');
        Route::get('/market', [BuyerController::class, 'market'])->name('market');
        Route::get('/orders', [BuyerController::class, 'orders'])->name('orders');
        Route::get('/bids', [BuyerController::class, 'bids'])->name('bids');
        
        // Quick view
        Route::get('/products/{product}/quick-view', [BuyerController::class, 'quickView'])->name('products.quick-view');
        
        // Purchase and bidding
        Route::post('/products/{product}/bid', [BuyerController::class, 'placeBid'])->name('place-bid');
        Route::post('/products/{product}/purchase', [BuyerController::class, 'purchase'])->name('purchase');
        
        // Cart routes
        Route::get('/cart', [BuyerController::class, 'cart'])->name('cart');
        Route::post('/cart/add/{product}', [BuyerController::class, 'addToCart'])->name('cart.add');
        Route::post('/cart/update/{product}', [BuyerController::class, 'updateCart'])->name('cart.update');
        Route::post('/cart/remove/{product}', [BuyerController::class, 'removeFromCart'])->name('cart.remove');
        Route::post('/cart/clear', [BuyerController::class, 'clearCart'])->name('cart.clear');
        
        // Checkout routes
        Route::get('/checkout/cart', [BuyerController::class, 'checkoutCart'])->name('checkout.cart');
        Route::post('/checkout/process', [BuyerController::class, 'processCartCheckout'])->name('checkout.process');
        Route::get('/checkout/{order}', [BuyerController::class, 'checkout'])->name('checkout'); // Single product checkout
        Route::get('/checkout/bid/{order}', [BuyerController::class, 'checkoutBidOrder'])->name('checkout.bid'); // Bid checkout
        
        // Payment
        Route::post('/payment/{order}', [BuyerController::class, 'processPayment'])->name('payment');
        // Temporary debug route - remove after testing
Route::get('/debug/bid-order/{order}', [BuyerController::class, 'debugBidOrder']);
        // Receipt download
        Route::get('/receipt/{order}/download', [BuyerController::class, 'downloadReceipt'])->name('receipt.download');
        
        // Tracking
        Route::get('/track-order/{order}', [BuyerController::class, 'trackOrder'])->name('track-order');
    });

    // Driver Routes
    Route::middleware(['role:driver'])->prefix('driver')->name('driver.')->group(function () {
        Route::get('/dashboard', [DriverController::class, 'dashboard'])->name('dashboard');
        Route::get('/deliveries', [DriverController::class, 'deliveries'])->name('deliveries');
        Route::get('/deliveries/{order}', [DriverController::class, 'showDelivery'])->name('deliveries.show');
        Route::post('/deliveries/{order}/update-status', [DriverController::class, 'updateDeliveryStatus'])->name('deliveries.update-status');
        Route::post('/location/update', [DriverController::class, 'updateLocation'])->name('location.update');
        Route::get('/location/current', [DriverController::class, 'getCurrentLocation'])->name('location.current');
        Route::post('/toggle-availability', [DriverController::class, 'toggleAvailability'])->name('toggle-availability');
    });
    
    // API Routes for real-time features
    Route::prefix('api')->name('api.')->group(function () {
        Route::get('/driver-location/{driver}', [DriverController::class, 'getCurrentLocation'])->name('driver-location');
        Route::get('/order-status/{order}', [BuyerController::class, 'getOrderStatus'])->name('order-status');
    });
});