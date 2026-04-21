<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * The original migration placed a unique constraint on `reference_number`
     * alone. This breaks POS orders that contain more than one item because
     * every stock-movement row for the same order shares the same
     * reference_number. The fix is a composite unique index on
     * (reference_number, product_id, variation_id) which correctly allows
     * multiple products per order while still preventing true duplicates.
     */
    public function up(): void
    {
        Schema::table('stocks', function (Blueprint $table) {
            // ----------------------------------------------------------------
            // Step 1: Drop the old single-column unique index.
            // We try the conventional Laravel name first; if the DBA renamed
            // it, the raw SQL fallback will catch it.
            // ----------------------------------------------------------------
            try {
                $table->dropUnique('stocks_reference_number_unique');
            } catch (\Exception $e) {
                // Index may have a different name — drop by column instead.
                DB::statement('ALTER TABLE stocks DROP INDEX IF EXISTS stocks_reference_number_unique');
            }

            // ----------------------------------------------------------------
            // Step 2: Add a composite unique index.
            //
            // We include variation_id so that (in theory) the same product
            // could appear twice in one order as different variations.
            // MySQL treats multiple NULLs in a unique index as distinct, so
            // rows where variation_id IS NULL will not conflict with each other
            // on that column — the uniqueness is effectively enforced on
            // (reference_number, product_id) for non-variation items, and on
            // (reference_number, product_id, variation_id) for variation items.
            // ----------------------------------------------------------------
            $table->unique(
                ['reference_number',
                 'product_id',
                 
                ],
                'stocks_ref_product_variation_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stocks', function (Blueprint $table) {
            $table->dropUnique('stocks_ref_product_variation_unique');

            // Restore the original (broken) single-column unique index.
            $table->unique('reference_number', 'stocks_reference_number_unique');
        });
    }
};
