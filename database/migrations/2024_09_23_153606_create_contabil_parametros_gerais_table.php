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
        Schema::create('contabil_parametros_gerais', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('issuer_id');
            $table->json('codigo');
            $table->json('descricao');
            $table->string('tipo');
            $table->json('debito');
            $table->json('descricao_debito');
            $table->json('credito');
            $table->json('descricao_credito');
            $table->string('hp');
            $table->string('complemento_historico');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parametros_conciliacao_bancaria');
    }
};
