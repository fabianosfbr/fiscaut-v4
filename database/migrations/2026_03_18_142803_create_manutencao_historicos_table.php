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
        Schema::create('manutencao_historicos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('manutencao_id')->constrained('manutencoes')->cascadeOnDelete();
            $table->foreignId('usuario_id')->constrained('users')->cascadeOnDelete();
            $table->string('acao', 50); // criacao, inicio, conclusao, cancelamento, reagendamento, comentario
            $table->string('status_anterior', 50)->nullable();
            $table->string('status_novo', 50)->nullable();
            $table->text('observacao')->nullable();
            $table->json('dados_alterados')->nullable(); // Para armazenar quais campos foram alterados
            $table->timestamp('created_at');

            // Índices para performance
            $table->index(['manutencao_id', 'created_at']);
            $table->index(['usuario_id']);
            $table->index(['acao']);
            $table->index(['created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('manutencao_historicos');
    }
};
