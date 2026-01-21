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
        Schema::table('simples_nacional_calculations', function (Blueprint $table) {
            $table->decimal('faturamento_periodo', 15, 2)->nullable()->after('faturamento_12_meses');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('simples_nacional_calculations', function (Blueprint $table) {
            $table->dropColumn('faturamento_periodo');
        });
    }
};
