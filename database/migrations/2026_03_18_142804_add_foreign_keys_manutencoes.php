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
        Schema::table('manutencoes', function (Blueprint $table) {
            // Adicionar foreign key para recorrencia_id após a tabela manutencao_recorrencias ser criada
            $table->foreign('recorrencia_id')->references('id')->on('manutencao_recorrencias')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('manutencoes', function (Blueprint $table) {
            $table->dropForeign(['recorrencia_id']);
        });
    }
};
