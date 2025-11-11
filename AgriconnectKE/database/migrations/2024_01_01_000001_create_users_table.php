<?php
// database/migrations/2024_01_01_000001_create_users_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->enum('role', ['farmer', 'buyer', 'driver', 'admin'])->default('buyer');
            $table->string('phone')->nullable();
            $table->text('address');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->boolean('is_available')->default(true);
            $table->string('avatar')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->string('timezone')->default('Africa/Nairobi');
            $table->rememberToken();
            $table->timestamps();
            
            // ADD COMPREHENSIVE INDEXES
            $table->index(['role', 'is_available']);
            $table->index(['latitude', 'longitude']);
            $table->index('created_at');
            $table->index('last_login_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('users');
    }
}