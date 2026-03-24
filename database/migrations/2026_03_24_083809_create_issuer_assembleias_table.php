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
        Schema::create('issuer_assembleias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('issuer_id')->constrained()->cascadeOnDelete();
            $table->string('document_path');
            $table->date('vigencia_date')->nullable();
            $table->date('data_limite_edital')->nullable();
            $table->string('prazo_tecnico')->nullable();
            $table->text('observacoes')->nullable();
            $table->enum('type', ['AGO', 'AGE'])->default('AGE');
            $table->date('data_limite_ago')->nullable();
            $table->string('prazo_tecnico_edital')->nullable();
            $table->date('mandato_fim')->nullable();
            $table->string('prazo_tecnico_mandato')->nullable();
            $table->date('mandato_conselho_fim')->nullable();
            $table->string('prazo_tecnico_mandato_conselho')->nullable();
            $table->date('mandato_banco_fim')->nullable();
            $table->string('prazo_tecnico_mandato_banco')->nullable();
            $table->integer('boleto_dia_vencimento')->nullable();
            $table->string('boleto_tipo_prazo')->nullable();
            $table->string('boleto_gerado_por')->nullable();
            $table->string('boleto_forma_rateio')->nullable();
            $table->json('tem_isencao_remuneracao')->nullable();
            $table->boolean('tem_isencao')->default(false);
            $table->boolean('tem_remuneracao')->default(false);
            $table->json('quem_recebe_isencao')->nullable();
            $table->json('quem_recebe_remuneracao')->nullable();
            $table->decimal('valor_isencao', 10, 2)->nullable();
            $table->decimal('valor_remuneracao', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('issuer_assembleias');
    }
};
