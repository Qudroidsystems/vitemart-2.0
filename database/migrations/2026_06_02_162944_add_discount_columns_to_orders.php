<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Add columns to orders table
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'subtotal')) {
                $table->decimal('subtotal', 15, 2)->default(0)->after('total_amount');
            }
            if (!Schema::hasColumn('orders', 'discount_amount')) {
                $table->decimal('discount_amount', 15, 2)->default(0)->after('subtotal');
            }
        });

        // Add columns to order_items table
        Schema::table('order_items', function (Blueprint $table) {
            if (!Schema::hasColumn('order_items', 'discount_type')) {
                $table->string('discount_type')->nullable()->after('total_price');
            }
            if (!Schema::hasColumn('order_items', 'discount_value')) {
                $table->decimal('discount_value', 15, 2)->default(0)->after('discount_type');
            }
            if (!Schema::hasColumn('order_items', 'discount_amount')) {
                $table->decimal('discount_amount', 15, 2)->default(0)->after('discount_value');
            }
            if (!Schema::hasColumn('order_items', 'is_unit_mode')) {
                $table->boolean('is_unit_mode')->default(false)->after('discount_amount');
            }
            if (!Schema::hasColumn('order_items', 'unit_name')) {
                $table->string('unit_name')->nullable()->after('is_unit_mode');
            }
            if (!Schema::hasColumn('order_items', 'unit_id')) {
                $table->unsignedBigInteger('unit_id')->nullable()->after('unit_name');
            }
        });

        // Create order_discounts table if not exists
        if (!Schema::hasTable('order_discounts')) {
            Schema::create('order_discounts', function (Blueprint $table) {
                $table->id();
                $table->string('order_id');
                $table->unsignedBigInteger('order_item_id')->nullable();
                $table->enum('type', ['order', 'item'])->default('order');
                $table->enum('discount_type', ['percent', 'fixed'])->default('percent');
                $table->decimal('discount_value', 15, 2)->default(0);
                $table->decimal('amount', 15, 2)->default(0);
                $table->unsignedBigInteger('applied_by')->nullable();
                $table->timestamp('applied_at')->nullable();
                $table->timestamps();

                $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
                $table->foreign('order_item_id')->references('id')->on('order_items')->onDelete('cascade');
                $table->foreign('applied_by')->references('id')->on('users')->onDelete('set null');

                $table->index(['order_id', 'type']);
                $table->index('order_item_id');
            });
        }
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['subtotal', 'discount_amount']);
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['discount_type', 'discount_value', 'discount_amount', 'is_unit_mode', 'unit_name', 'unit_id']);
        });

        Schema::dropIfExists('order_discounts');
    }
};
