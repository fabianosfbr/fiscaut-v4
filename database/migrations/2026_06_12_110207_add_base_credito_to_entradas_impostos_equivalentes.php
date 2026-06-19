<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('entradas_impostos_equivalentes', function (Blueprint $table) {
            $table->string('base_credito', 2)->nullable()->after('status_ipi')
                ->comment('Código de crédito PIS/COFINS para o campo 67 do 1030 (01-18)');
        });

        // Povoar com os valores padrão baseados no código da etiqueta
        // Fonte: tabela_etiquetas.py do Python (v22r)
        $baseCreditoMap = [
            '101' => '12',  // Devolução de vendas → cod 108 (field)
            // Mapeamento por código de etiqueta (campo `tag` da entradas_impostos_equivalentes)
        ];

        // Mapeamento: codigo_etiqueta → base_credito_campo67
        // Copiado da tabela_etiquetas.py
        $etiquetaMapping = [
            8647 => '02',  // Materia Prima no Mercado Interno
            8655 => '02',  // Materia Prima no Mercado Externo
            8664 => '02',  // Material de Embalagem no Mercado Interno
            8784 => '02',  // Produtos em Elaboracao
            8681 => '03',  // Industrializacao Efetuada para Terceiros
            8758 => '04',  // Energia Eletrica (GGF)
            9062 => '13',  // Combustiveis e Lubrificantes (GGF)
            9059 => '13',  // Combustiveis e Lubrificantes (MOD)
            10586 => '13', // Combustiveis e Lubrificantes (MOS)
            12724 => '13', // Combustiveis e Lubrificantes (CSP)
            8719 => '13',  // Material Auxiliar Indireto c/Credito
            358 => '12',  // Devolucoes de Vendas
        ];

    }

    public function down(): void
    {
        Schema::table('entradas_impostos_equivalentes', function (Blueprint $table) {
            $table->dropColumn('base_credito');
        });
    }
};
