<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Check if order_id column exists but doesn't have foreign key
        if (Schema::hasColumn('bids', 'order_id')) {
            // Check if foreign key doesn't exist
            $foreignKeys = DB::select("
                SELECT CONSTRAINT_NAME 
                FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                WHERE TABLE_NAME = 'bids' 
                AND COLUMN_NAME = 'order_id'
                AND REFERENCED_TABLE_NAME IS NOT NULL
            ");
            
            if (empty($foreignKeys)) {
                Schema::table('bids', function (Blueprint $table) {
                    $table->foreign('order_id')
                          ->references('id')
                          ->on('orders')
                          ->onDelete('set null');
                });
                echo "✅ Added foreign key to bids.order_id\n";
            } else {
                echo "✅ Foreign key already exists on bids.order_id\n";
            }
        } else {
            echo "❌ order_id column not found in bids table\n";
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('bids', 'order_id')) {
            Schema::table('bids', function (Blueprint $table) {
                $table->dropForeign(['order_id']);
            });
        }
    }
};