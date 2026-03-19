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
        Schema::create('tipos_manutencao', function (Blueprint $table) {
            $table->id();
            $table->foreignId('issuer_id')->constrained('issuers')->cascadeOnDelete();
            $table->string('nome');
            $table->string('descricao');
            $table->string('categoria', 50)->default('preventiva');
            $table->string('periodicidade_padrao', 50)->nullable();
            $table->integer('alerta_dias_antecedencia')->default(7);
            $table->string('prioridade', 50)->default('media');
            $table->string('responsavel_padrao')->nullable();
            $table->boolean('ativo')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tipos_manutencao');
    }
};
