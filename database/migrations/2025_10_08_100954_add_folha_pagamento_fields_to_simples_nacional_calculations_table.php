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
        Schema::table('simples_nacional_calculations', function (Blueprint $table) {
            // Campos para cálculo do Fator R
            $table->decimal('folha_salarios_12_meses', 15, 2)->nullable()->after('faturamento_periodo')
                ->comment('Folha de salários dos últimos 12 meses incluindo encargos');

            $table->decimal('fator_r', 5, 4)->nullable()->after('folha_salarios_12_meses')
                ->comment('Fator R calculado (folha/receita bruta)');

            $table->boolean('sujeito_fator_r')->default(false)->after('fator_r')
                ->comment('Indica se o CNAE está sujeito ao Fator R');

            $table->string('anexo_fator_r', 10)->nullable()->after('sujeito_fator_r')
                ->comment('Anexo resultante do cálculo do Fator R (III ou V)');

            // Detalhamento da folha de pagamento (JSON)
            $table->json('detalhamento_folha')->nullable()->after('anexo_fator_r')
                ->comment('Detalhamento dos componentes da folha de pagamento');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('simples_nacional_calculations', function (Blueprint $table) {
            $table->dropColumn([
                'folha_salarios_12_meses',
                'fator_r',
                'sujeito_fator_r',
                'anexo_fator_r',
                'detalhamento_folha',
            ]);
        });
    }
};
