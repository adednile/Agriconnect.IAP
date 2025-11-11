<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReviewsTable extends Migration
{
    public function up()
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('reviewer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('reviewee_id')->constrained('users')->onDelete('cascade');
            $table->enum('type', ['product', 'farmer', 'buyer', 'driver']);
            $table->tinyInteger('rating');
            $table->text('comment');
            $table->json('images')->nullable();
            $table->boolean('is_approved')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->integer('helpful_count')->default(0);
            $table->softDeletes();
            $table->timestamps();
            
            $table->index(['reviewee_id', 'type', 'is_approved']);
            $table->index(['reviewer_id', 'type']);
            $table->index('rating');
            $table->unique(['order_id', 'reviewer_id', 'type']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('reviews');
    }
}