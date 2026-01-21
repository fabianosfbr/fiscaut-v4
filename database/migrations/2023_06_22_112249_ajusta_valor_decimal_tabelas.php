<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('nfes', function (Blueprint $table) {
            $table->decimal('vNfe', 14, 4)->change();
            $table->decimal('vBC', 14, 4)->change();
            $table->decimal('vICMS', 14, 4)->change();
            $table->decimal('vICMSDeson', 14, 4)->change();
            $table->decimal('vFCPUFDest', 14, 4)->change();
            $table->decimal('vICMSUFDest', 14, 4)->change();
            $table->decimal('vICMSUFRemet', 14, 4)->change();
            $table->decimal('vFCP', 14, 4)->change();
            $table->decimal('vBCST', 14, 4)->change();
            $table->decimal('vST', 14, 4)->change();
            $table->decimal('vFCPST', 14, 4)->change();
            $table->decimal('vFCPSTRet', 14, 4)->change();
            $table->decimal('vProd', 14, 4)->change();
            $table->decimal('vFrete', 14, 4)->change();
            $table->decimal('vSeg', 14, 4)->change();
            $table->decimal('vDesc', 14, 4)->change();
            $table->decimal('vII', 14, 4)->change();
            $table->decimal('vIPI', 14, 4)->change();
            $table->decimal('vIPIDevol', 14, 4)->change();
            $table->decimal('vPIS', 14, 4)->change();
            $table->decimal('vCOFINS', 14, 4)->change();
            $table->decimal('vOutro', 14, 4)->change();
            $table->decimal('vTotTrib', 14, 4)->change();
        });

        Schema::table('nfe_products', function (Blueprint $table) {
            $table->decimal('quantidade', 14, 4)->change();
            $table->decimal('valor_unit', 14, 4)->change();
            $table->decimal('valor_total', 14, 4)->change();
            $table->decimal('valor_desc', 14, 4)->change();
            $table->decimal('base_icms', 14, 4)->change();
            $table->decimal('valor_icms', 14, 4)->change();
            $table->decimal('aliq_icms', 14, 4)->change();
            $table->decimal('base_ipi', 14, 4)->change();
            $table->decimal('valor_ipi', 14, 4)->change();
            $table->decimal('aliq_ipi', 14, 4)->change();
        });

        Schema::table('ctes', function (Blueprint $table) {
            $table->decimal('vCTe', 14, 4)->change();
        });

        Schema::table('nfses', function (Blueprint $table) {
            $table->decimal('valor_servico', 14, 4)->change();
        });

        Schema::table('tagging_tagged', function (Blueprint $table) {
            $table->decimal('value', 14, 4)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
