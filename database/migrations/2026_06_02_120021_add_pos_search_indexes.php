<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (!$this->indexExists('products', 'products_is_active_title_index')) {
                $table->index(['is_active', 'title'], 'products_is_active_title_index');
            }
            if (!$this->indexExists('products', 'products_barcode_index')) {
                $table->index('barcode', 'products_barcode_index');
            }
            if (!$this->indexExists('products', 'products_sku_index')) {
                $table->index('sku', 'products_sku_index');
            }
            if (!$this->indexExists('products', 'products_is_active_stock_index')) {
                $table->index(['is_active', 'stock'], 'products_is_active_stock_index');
            }
        });

        Schema::table('product_variations', function (Blueprint $table) {
            if (!$this->indexExists('product_variations', 'product_variations_barcode_index')) {
                $table->index('barcode', 'product_variations_barcode_index');
            }
            if (!$this->indexExists('product_variations', 'product_variations_sku_index')) {
                $table->index('sku', 'product_variations_sku_index');
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('products_is_active_title_index');
            $table->dropIndex('products_barcode_index');
            $table->dropIndex('products_sku_index');
            $table->dropIndex('products_is_active_stock_index');
        });

        Schema::table('product_variations', function (Blueprint $table) {
            $table->dropIndex('product_variations_barcode_index');
            $table->dropIndex('product_variations_sku_index');
        });
    }

    private function indexExists(string $table, string $index): bool
    {
        try {
            $conn = Schema::getConnection();
            $sm   = $conn->getDoctrineSchemaManager();
            $indexes = $sm->listTableIndexes($table);
            return array_key_exists($index, $indexes);
        } catch (\Exception $e) {
            return false;
        }
    }
};
