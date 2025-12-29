<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('customer_point_transactions', function (Blueprint $table) {
            $table->id();

            // Customer - standard big integer foreign key
            $table->foreignId('customer_id')
                  ->constrained('customers')
                  ->onDelete('cascade');

            // Order - string to match orders.id (custom string PK)
            $table->string('order_id', 255)->nullable();  // Match length of orders.id
            $table->foreign('order_id')
                  ->references('id')
                  ->on('orders')
                  ->onDelete('set null');

            // Points
            $table->integer('points_earned')->default(0);
            $table->integer('points_redeemed')->default(0);

            // Money
            $table->decimal('amount_spent', 15, 2)->default(0.00);
            $table->decimal('discount_applied', 15, 2)->default(0.00);

            // Description
            $table->string('description');

            // Created by (cashier/admin)
            $table->foreignId('created_by')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('set null');

            // Timestamps
            $table->timestamps();

            // Indexes for performance
            $table->index('customer_id');
            $table->index('order_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_point_transactions');
    }
};
