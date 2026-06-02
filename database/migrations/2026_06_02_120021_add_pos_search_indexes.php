<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Check each index before creating
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
            $table->dropIndexIfExists('products_is_active_title_index');
            $table->dropIndexIfExists('products_barcode_index');
            $table->dropIndexIfExists('products_sku_index');
            $table->dropIndexIfExists('products_is_active_stock_index');
        });

        Schema::table('product_variations', function (Blueprint $table) {
            $table->dropIndexIfExists('product_variations_barcode_index');
            $table->dropIndexIfExists('product_variations_sku_index');
        });
    }

    private function indexExists(string $table, string $indexName): bool
    {
        try {
            $connection = Schema::getConnection();
            $database = $connection->getDatabaseName();

            // Direct SQL query to check if index exists (works with MySQL)
            $result = DB::select(
                "SELECT COUNT(*) as count FROM information_schema.statistics
                 WHERE table_schema = ?
                 AND table_name = ?
                 AND index_name = ?",
                [$database, $table, $indexName]
            );

            return (int) ($result[0]->count ?? 0) > 0;
        } catch (\Exception $e) {
            // Fallback to Doctrine method if SQL approach fails
            try {
                $sm = Schema::getConnection()->getDoctrineSchemaManager();
                $indexes = $sm->listTableIndexes($table);
                return array_key_exists($indexName, $indexes);
            } catch (\Exception $e2) {
                return false;
            }
        }
    }
};
