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
        Schema::table('nfe_products', function (Blueprint $table) {
            // Índice composto para otimizar consultas por NFE e CFOP
            $table->index(['nfe_id', 'cfop'], 'idx_nfe_products_nfe_cfop');

            // Índice no campo CFOP para joins com tabela cfops
            $table->index('cfop', 'idx_nfe_products_cfop');

            // Índice no valor_total para agregações
            $table->index('valor_total', 'idx_nfe_products_valor_total');
        });

        Schema::table('nfes', function (Blueprint $table) {
            // Índice composto para consultas por empresa e período
            $table->index(['emitente_cnpj', 'data_emissao'], 'idx_nfes_emitente_cnpj_data');

            // Índice composto para consultas por empresa, tipo e período
            $table->index(['emitente_cnpj', 'tpNf', 'data_emissao'], 'idx_nfes_emitente_cnpj_tipo_data');

            // Índice composto para consultas por empresa e período
            $table->index(['destinatario_cnpj', 'data_emissao'], 'idx_nfes_destinatario_cnpj_data');

            // Índice composto para consultas por empresa, tipo e período
            $table->index(['destinatario_cnpj', 'tpNf', 'data_emissao'], 'idx_nfes_destinatario_cnpj_tipo_data');

            // Índice no campo tenant_id se existir (para multi-tenancy)
            if (Schema::hasColumn('nfes', 'tenant_id')) {
                $table->index('tenant_id', 'idx_nfes_tenant_id');
            }
        });

        Schema::table('cfops', function (Blueprint $table) {
            // Índice no campo codigo para joins otimizados
            $table->index('codigo', 'idx_cfops_codigo');

            // Índice no campo is_faturamento para filtros rápidos
            $table->index('is_faturamento', 'idx_cfops_is_faturamento');

            // Índice composto para consultas por faturamento e anexo
            $table->index(['is_faturamento', 'anexo'], 'idx_cfops_faturamento_anexo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nfe_products', function (Blueprint $table) {
            $table->dropIndex('idx_nfe_products_nfe_cfop');
            $table->dropIndex('idx_nfe_products_cfop');
            $table->dropIndex('idx_nfe_products_valor_total');
        });

        Schema::table('nfes', function (Blueprint $table) {
            $table->dropIndex('idx_nfes_emitente_cnpj_data');
            $table->dropIndex('idx_nfes_emitente_cnpj_tipo_data');
            $table->dropIndex('idx_nfes_destinatario_cnpj_data');
            $table->dropIndex('idx_nfes_destinatario_cnpj_tipo_data');

            if (Schema::hasColumn('nfes', 'tenant_id')) {
                $table->dropIndex('idx_nfes_tenant_id');
            }
        });

        Schema::table('cfops', function (Blueprint $table) {
            $table->dropIndex('idx_cfops_codigo');
            $table->dropIndex('idx_cfops_is_faturamento');
            $table->dropIndex('idx_cfops_faturamento_anexo');
        });
    }
};
