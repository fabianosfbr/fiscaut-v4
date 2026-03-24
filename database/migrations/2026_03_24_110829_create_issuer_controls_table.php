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
        Schema::create('issuer_controls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('issuer_id')->constrained('issuers')->cascadeOnDelete();
            $table->foreignId('type_control_id')->constrained('issuer_control_types')->cascadeOnDelete();
            $table->foreignId('fornecedor_id')->nullable()->constrained('contabil_fornecedores')->nullOnDelete();
            $table->string('titulo', 200);
            $table->text('descricao')->nullable();
            $table->string('tipo', 50)->default('preventiva'); // preventiva, corretiva
            $table->string('status', 50)->default('programada'); // programada, em_andamento, concluida, cancelada, atrasada
            $table->string('prioridade', 50)->default('media'); // baixa, media, alta, critica
            $table->date('data_programada');
            $table->datetime('data_execucao')->nullable();
            $table->datetime('data_conclusao')->nullable();
            $table->decimal('custo_estimado', 10, 2)->nullable();
            $table->decimal('custo_real', 10, 2)->nullable();
            $table->unsignedBigInteger('recorrencia_id')->nullable();
            $table->string('local', 200)->nullable();
            $table->string('equipamento', 200)->nullable();
            $table->json('anexos')->nullable();
            $table->text('observacoes')->nullable();
            $table->string('usuario_responsavel')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Índices para performance
            $table->index(['issuer_id', 'status']);
            $table->index(['issuer_id', 'data_programada']);
            $table->index(['type_control_id']);
            $table->index(['status']);
            $table->index(['prioridade']);
            $table->index(['data_programada']);
            $table->index(['recorrencia_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('issuer_controls');
    }
};
