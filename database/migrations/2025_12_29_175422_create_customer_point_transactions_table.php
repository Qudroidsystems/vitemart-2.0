<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Temporarily disable foreign key checks to bypass strict validation
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        Schema::create('customer_point_transactions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('customer_id')
                  ->constrained('customers')
                  ->onDelete('cascade');

            $table->string('order_id', 255)->nullable();
            $table->foreign('order_id')
                  ->references('id')
                  ->on('orders')
                  ->onDelete('set null');

            $table->integer('points_earned')->default(0);
            $table->integer('points_redeemed')->default(0);
            $table->decimal('amount_spent', 15, 2)->default(0.00);
            $table->decimal('discount_applied', 15, 2)->default(0.00);
            $table->string('description');

            $table->foreignId('created_by')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('set null');

            $table->timestamps();

            $table->index('customer_id');
            $table->index('order_id');
            $table->index('created_at');
        });

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_point_transactions');
    }
};
