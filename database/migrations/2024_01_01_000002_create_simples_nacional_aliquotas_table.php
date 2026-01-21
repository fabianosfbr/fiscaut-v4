<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('simples_nacional_aliquotas', function (Blueprint $table) {
            $table->id();
            $table->string('anexo', 5);
            $table->decimal('faixa_inicial', 15, 2);
            $table->decimal('faixa_final', 15, 2);
            $table->decimal('aliquota', 8, 4);
            $table->decimal('valor_deduzir', 12, 2);
            $table->decimal('irpj_percentual', 5, 2)->nullable();
            $table->decimal('csll_percentual', 5, 2)->nullable();
            $table->decimal('cofins_percentual', 5, 2)->nullable();
            $table->decimal('pis_percentual', 5, 2)->nullable();
            $table->decimal('cpp_percentual', 5, 2)->nullable();
            $table->decimal('icms_percentual', 5, 2)->nullable();
            $table->decimal('iss_percentual', 5, 2)->nullable();
            $table->timestamps();

            $table->foreign('anexo')->references('anexo')->on('simples_nacional_anexos');
            $table->index(['anexo', 'faixa_inicial', 'faixa_final']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('simples_nacional_aliquotas');
    }
};
