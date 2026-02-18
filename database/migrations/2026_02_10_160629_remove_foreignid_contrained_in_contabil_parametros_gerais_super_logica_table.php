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
        Schema::table('contabil_parametros_gerais_super_logica', function (Blueprint $table) {
            $table->dropForeign(['codigo_historico']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contabil_parametros_gerais_super_logica', function (Blueprint $table) {
            $table->foreignId('codigo_historico')->constrained('contabil_historico_contabeis');
        });
    }
};
