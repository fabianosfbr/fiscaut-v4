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
        Schema::table('contabil_parametros_gerais', function (Blueprint $table) {
            $table->integer('conta_contabil')->change();
            $table->dropColumn('complemento_historico');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contabil_parametros_gerais', function (Blueprint $table) {
            $table->json('conta_contabil')->change();
            $table->json('complemento_historico');
        });
    }
};
