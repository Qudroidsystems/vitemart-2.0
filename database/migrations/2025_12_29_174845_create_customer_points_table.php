<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('customer_points', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->integer('points')->default(0);
            $table->decimal('points_value', 15, 2)->default(0.00); // Cached monetary value
            $table->timestamps();

            $table->unique('customer_id'); // One row per customer
        });

        // Points transaction history
        Schema::create('customer_point_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->foreignId('order_id')->nullable()->constrained('orders')->onDelete('set null');
            $table->integer('points_earned')->default(0);
            $table->integer('points_redeemed')->default(0);
            $table->decimal('amount_spent', 15, 2)->default(0.00);
            $table->decimal('discount_applied', 15, 2)->default(0.00);
            $table->string('description');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('customer_point_transactions');
        Schema::dropIfExists('customer_points');
    }
};
