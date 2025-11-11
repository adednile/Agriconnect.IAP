<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMissingColumnsToTables extends Migration
{
    public function up()
    {
        // Add to users table
        if (!Schema::hasColumn('users', 'email_verified_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->timestamp('email_verified_at')->nullable()->after('email');
                $table->string('avatar')->nullable()->after('is_available');
                $table->timestamp('last_login_at')->nullable()->after('remember_token');
            });
        }

        // Add to products table
        if (!Schema::hasColumn('products', 'slug')) {
            Schema::table('products', function (Blueprint $table) {
                $table->string('slug')->unique()->nullable()->after('name');
                $table->decimal('original_price', 10, 2)->nullable()->after('price');
                $table->string('unit')->default('kg')->after('quantity');
                $table->boolean('is_featured')->default(false)->after('accepts_bids');
                $table->softDeletes();
            });
        }

        // Add to orders table
        if (!Schema::hasColumn('orders', 'order_number')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->string('order_number')->unique()->nullable()->after('id');
                $table->decimal('product_price', 10, 2)->default(0)->after('bid_id');
                $table->decimal('subtotal', 10, 2)->default(0)->after('product_price');
                $table->softDeletes();
            });
        }
    }

    public function down()
    {
        // Remove added columns if needed
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['email_verified_at', 'avatar', 'last_login_at']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['slug', 'original_price', 'unit', 'is_featured']);
            $table->dropSoftDeletes();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['order_number', 'product_price', 'subtotal']);
            $table->dropSoftDeletes();
        });
    }
}