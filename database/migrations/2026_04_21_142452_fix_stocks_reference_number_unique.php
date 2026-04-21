<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
   public function up()
    {
        Schema::table('stocks', function (Blueprint $table) {
            // Drop the unique constraint (the exact name might vary)
            // Common names: 'stocks_reference_number_unique' or 'stocks_reference_number_unique'
            $table->dropUnique('stocks_reference_number_unique');

            // Add a regular index for performance (not unique)
            $table->index('reference_number');

            // Optional: Create a composite index for common queries
            $table->index(['reference_number', 'reference_type']);
            $table->index(['product_id', 'reference_number']);
        });
    }

    public function down()
    {
        Schema::table('stocks', function (Blueprint $table) {
            $table->dropIndex(['reference_number']);
            $table->dropIndex(['reference_number', 'reference_type']);
            $table->dropIndex(['product_id', 'reference_number']);

            // Re-add the unique constraint
            $table->unique('reference_number');
        });
    }
};
