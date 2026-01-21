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
        DB::statement('DROP VIEW IF EXISTS `nfe_tag_agregador_view`;');
        DB::statement("
                    CREATE VIEW nfe_tag_agregador_view
                        AS
                        SELECT
                            `categories_tag`.`issuer_id` AS `issuer_id`,
                            `tagging_tagged`.`tenant_id` AS `tenant_id`,
                            `categories_tag`.`name` AS `category`,
                            `categories_tag`.`color` AS `color`,
                            `tagging_tags`.`code` AS `code`,
                            `tagging_tags`.`name` AS `tag`,
                            `tagging_tagged`.`value` AS `value`,
                            `nfe` AS `tipo`,
                            `nfes`.`nNF` AS `nNF`,
	                        `nfes`.`data_emissao` AS `data_emissao`,
                            `nfes`.`data_entrada` AS `data_entrada`,
                            `nfes`.`vNfe` AS `vNfe`,
                            `nfes`.`vBC` AS `vBC`,
                            `nfes`.`vICMS` AS `vICMS`,
                            `nfes`.`vICMSDeson` AS `vICMSDeson`,
                            `nfes`.`vFCPUFDest` AS `vFCPUFDest`,
                            `nfes`.`vICMSUFDest` AS `vICMSUFDest`,
                            `nfes`.`vICMSUFRemet` AS `vICMSUFRemet`,
                            `nfes`.`vFCP` AS `vFCP`,
                            `nfes`.`vBCST` AS `vBCST`,
                            `nfes`.`vST` AS `vST`,
                            `nfes`.`vFCPST` AS `vFCPST`,
                            `nfes`.`vFCPSTRet` AS `vFCPSTRet`,
                            `nfes`.`vProd` AS `vProd`,
                            `nfes`.`vFrete` AS `vFrete`,
                            `nfes`.`vSeg` AS `vSeg`,
                            `nfes`.`vDesc` AS `vDesc`,
                            `nfes`.`vII` AS `vII`,
                            `nfes`.`vIPI` AS `vIPI`,
                            `nfes`.`vIPIDevol` AS `vIPIDevol`,
                            `nfes`.`vPIS` AS `vPIS`,
                            `nfes`.`vCOFINS` AS `vCOFINS`,
                            `nfes`.`vOutro` AS `vOutro`,
                            `nfes`.`vTotTrib` AS `vTotTrib`
                        FROM
                            (((
                                        `categories_tag`
                                        JOIN `tagging_tags` ON ((
                                                `categories_tag`.`id` = `tagging_tags`.`category_id`
                                            )))
                                    JOIN `tagging_tagged` ON ((
                                            `tagging_tags`.`id` = `tagging_tagged`.`tag_id`
                                        )))
                                JOIN `nfes` ON ((
                                        `tagging_tagged`.`taggable_id` = `nfes`.`id`
                                    )))
                        WHERE
                            (
                            `tagging_tagged`.`taggable_type` = 'App\\\Models\\\NotaFiscalEletronica')
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS `nfe_tag_agregador_view`;');
    }
};
