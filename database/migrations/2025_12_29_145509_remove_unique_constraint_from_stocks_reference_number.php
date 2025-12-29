<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stocks', function (Blueprint $table) {
            // Drop the unique index on reference_number
            $table->dropUnique(['reference_number']);

            // Optional: If you want to prevent true duplicates, use a composite unique key
            // $table->unique(['reference_number', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::table('stocks', function (Blueprint $table) {
            $table->unique('reference_number');
        });
    }
};
