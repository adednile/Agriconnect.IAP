<?php
// database/migrations/2024_01_01_000002_create_products_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farmer_id')->constrained('users')->onDelete('cascade');
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description');
            $table->text('short_description')->nullable();
            $table->string('image')->nullable();
            $table->json('gallery_images')->nullable();
            $table->decimal('price', 10, 2);
            $table->decimal('original_price', 10, 2)->nullable();
            $table->integer('quantity');
            $table->integer('min_quantity')->default(1);
            $table->integer('max_quantity')->default(100);
            $table->string('unit')->default('kg');
            $table->string('category');
            $table->string('subcategory')->nullable();
            $table->boolean('is_available')->default(true);
            $table->boolean('accepts_bids')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->integer('view_count')->default(0);
            $table->integer('order_count')->default(0);
            $table->decimal('rating_avg', 3, 2)->default(0);
            $table->integer('rating_count')->default(0);
            $table->json('specifications')->nullable();
            $table->softDeletes();
            $table->timestamps();
            
            // COMPREHENSIVE INDEXING
            $table->index(['farmer_id', 'is_available', 'deleted_at']);
            $table->index(['category', 'is_available', 'deleted_at']);
            $table->index(['is_available', 'accepts_bids', 'deleted_at']);
            $table->index(['is_featured', 'is_available', 'deleted_at']);
            $table->index(['price', 'is_available', 'deleted_at']);
            $table->index(['rating_avg', 'is_available', 'deleted_at']);
            $table->index('created_at');
            $table->index('view_count');
            $table->index('order_count');
            $table->unique(['farmer_id', 'name', 'deleted_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('products');
    }
}