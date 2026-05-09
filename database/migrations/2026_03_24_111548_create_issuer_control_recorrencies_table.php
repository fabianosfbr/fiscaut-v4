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
        Schema::create('issuer_control_recorrencies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('issuer_id')->constrained('issuers')->cascadeOnDelete();
            $table->bigInteger('type_control_id');
            $table->string('titulo_template', 200);
            $table->text('descricao_template')->nullable();
            $table->string('frequencia', 50); // diaria, semanal, quinzenal, mensal, bimestral, trimestral, semestral, anual
            $table->integer('dia_mes')->nullable(); // Dia do mês (1-31)
            $table->integer('dia_semana')->nullable(); // Dia da semana (0-6, 0=domingo)
            $table->integer('mes')->nullable(); // Mês específico (1-12) para frequência anual
            $table->integer('intervalo')->default(1); // Intervalo (ex: a cada 2 meses)
            $table->date('data_inicio');
            $table->date('data_fim')->nullable();
            $table->integer('gerar_dias_antecedencia')->default(30); // Dias antes para gerar a manutenção
            $table->boolean('ativo')->default(true);
            $table->date('ultima_geracao')->nullable();
            $table->date('proxima_geracao')->nullable();
            $table->timestamps();

            // Índices para performance
            $table->index(['issuer_id', 'ativo']);
            $table->index(['type_control_id']);
            $table->index(['frequencia']);
            $table->index(['ativo']);
            $table->index(['proxima_geracao']);
            $table->index(['data_inicio', 'data_fim']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('issuer_control_recorrencies');
    }
};
