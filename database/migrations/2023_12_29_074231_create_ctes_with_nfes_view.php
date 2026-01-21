<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('DROP VIEW IF EXISTS `ctes_with_nfes_view`;');
        DB::statement("
                    CREATE VIEW ctes_with_nfes_view
                        AS
                        SELECT
                            c.id AS id,
                            c.chave AS cte_chave,
                            c.emitente_razao_social AS cte_emitente_razao_social,
                            c.emitente_cnpj AS cte_emitente_cnpj,
                            c.destinatario_razao_social AS cte_destinatario_razao_social,
                            c.destinatario_cnpj AS cte_destinatario_cnpj,
                            c.remetente_razao_social AS cte_remetente_razao_social,
                            c.remetente_cnpj AS cte_rementente_cnpj,
                            c.tomador_razao_social AS cte_tomador_razao_social,
                            c.tomador_cnpj AS cte_tomador_cnpj,
                            c.data_emissao AS cte_data_emissao,
                            c.nCTe AS cte_numero,
                            c.vCTe AS cte_valor,
                            c.status_cte AS cte_status,
                            n.chave AS nfe_chave,
                            n.emitente_razao_social AS nfe_emitente_razao_social,
                            n.emitente_cnpj AS nfe_emitente_cnpj,
                            n.destinatario_razao_social AS nfe_destinatario_razao_social,
                            n.destinatario_cnpj AS nfe_destinatario_cnpj,
                            n.vNfe AS nfe_valor,
                            n.nNF AS nfe_numero,
                            n.data_emissao AS nfe_data_emissao,
                            n.tpNf AS nfe_tipo
                        FROM
                            ctes AS c
                            INNER JOIN nfes AS n ON json_unquote(
                            json_extract( c.nfe_chave, '$.\"chave\"' )) = n.chave
                            AND c.tenant_id = n.tenant_id
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS `ctes_with_nfes_view`;');
    }
};
