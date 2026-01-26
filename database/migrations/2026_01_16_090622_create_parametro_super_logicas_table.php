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
        Schema::create('contabil_parametros_gerais_super_logica', function (Blueprint $table) {
            $table->id();
            $table->json('params');
            $table->foreignId('issuer_id')->constrained('issuers');
            $table->foreignId('conta_credito')->constrained('contabil_plano_de_contas');
            $table->foreignId('conta_debito')->constrained('contabil_plano_de_contas');
            $table->foreignId('codigo_historico')->constrained('contabil_historico_contabeis');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contabil_parametros_gerais_super_logica');
    }
};
