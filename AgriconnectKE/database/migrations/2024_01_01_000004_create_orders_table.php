<?php
// database/migrations/2024_01_01_000004_create_orders_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('buyer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('farmer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('driver_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('bid_id')->nullable()->constrained()->onDelete('set null');
            
            // PRICING FIELDS
            $table->decimal('product_price', 10, 2);
            $table->decimal('bid_amount', 10, 2)->nullable();
            $table->decimal('subtotal', 10, 2);
            $table->decimal('delivery_cost', 8, 2)->default(0);
            $table->decimal('tax_amount', 8, 2)->default(0);
            $table->decimal('total_amount', 10, 2);
            $table->integer('quantity');
            
            // ORDER STATUS
            $table->enum('status', [
                'pending', 'paid', 'confirmed', 'preparing', 'ready_for_pickup', 
                'shipped', 'out_for_delivery', 'delivered', 'cancelled', 'refunded'
            ])->default('pending');
            
            // PAYMENT INFORMATION
            $table->string('mpesa_receipt')->nullable();
            $table->string('payment_method')->default('mpesa');
            $table->enum('payment_status', ['pending', 'paid', 'failed', 'refunded'])->default('pending');
            $table->text('payment_metadata')->nullable();
            
            // DELIVERY INFORMATION
            $table->text('delivery_address');
            $table->decimal('delivery_lat', 10, 8);
            $table->decimal('delivery_lng', 11, 8);
            $table->text('delivery_instructions')->nullable();
            $table->timestamp('estimated_delivery_time')->nullable();
            
            // ORDER LIFECYCLE TIMESTAMPS
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('out_for_delivery_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();
            
            // RATING AND FEEDBACK
            $table->tinyInteger('buyer_rating')->nullable();
            $table->text('buyer_feedback')->nullable();
            $table->tinyInteger('farmer_rating')->nullable();
            $table->text('farmer_feedback')->nullable();
            $table->tinyInteger('driver_rating')->nullable();
            $table->text('driver_feedback')->nullable();
            
            $table->softDeletes();
            $table->timestamps();
            
            // COMPREHENSIVE INDEXING
            $table->index(['buyer_id', 'status', 'created_at']);
            $table->index(['farmer_id', 'status', 'created_at']);
            $table->index(['driver_id', 'status', 'created_at']);
            $table->index(['status', 'payment_status']);
            $table->index('order_number');
            $table->index('created_at');
            $table->index('paid_at');
            $table->index('delivered_at');
            $table->index(['estimated_delivery_time', 'status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('orders');
    }
}