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
            $table->json('data_source_parametros_gerais_target_columns')->nullable()->after('data_source_search_constant');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contabil_layout_rules', function (Blueprint $table) {
            $table->dropColumn('data_source_parametros_gerais_target_columns');
        });
    }
};
