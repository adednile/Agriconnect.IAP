<?php
// database/migrations/2024_01_01_000003_create_bids_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBidsTable extends Migration
{
    public function up()
    {
        Schema::create('bids', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('buyer_id')->constrained('users')->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->enum('status', ['pending', 'accepted', 'rejected', 'expired', 'cancelled'])->default('pending');
            $table->text('message')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
            
            // COMPREHENSIVE INDEXING
            $table->index(['product_id', 'status', 'created_at']);
            $table->index(['buyer_id', 'status', 'created_at']);
            $table->index(['status', 'expires_at']);
            $table->index('created_at');
            $table->index('expires_at');
            
            // PREVENT DUPLICATE ACTIVE BIDS
            $table->unique(['product_id', 'buyer_id', 'status'], 'unique_active_bid');
            
            // ADD FOREIGN KEY FOR ORDER RELATIONSHIP
            $table->foreignId('order_id')->nullable()->constrained('orders')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('bids');
    }
}