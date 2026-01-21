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
        Schema::table('contabil_layout_rules', function (Blueprint $table) {
            $table->json('data_source_historical_columns')->nullable()->after('default_value');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contabil_layout_rules', function (Blueprint $table) {
            $table->dropColumn('data_source_historical_columns');
        });
    }
};
