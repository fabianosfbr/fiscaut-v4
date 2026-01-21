<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('simples_nacional_calculations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('issuer_id')->constrained('issuers');
            $table->date('periodo_apuracao');
            $table->decimal('faturamento_12_meses', 15, 2);
            $table->string('anexo', 5);
            $table->integer('faixa_receita');
            $table->decimal('aliquota_efetiva', 8, 4);
            $table->decimal('valor_das', 12, 2);
            $table->json('detalhamento_impostos');
            $table->enum('status', ['calculado', 'pago', 'cancelado'])->default('calculado');
            $table->timestamps();

            $table->foreign('anexo')->references('anexo')->on('simples_nacional_anexos');
            $table->index(['issuer_id', 'periodo_apuracao']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('simples_nacional_calculations');
    }
};
