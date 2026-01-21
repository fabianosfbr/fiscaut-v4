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

            $table->decimal('vBC', 10, 4)->default(0);
            $table->decimal('vICMS', 10, 4)->default(0);
            $table->decimal('vICMSDeson', 10, 4)->default(0);
            $table->decimal('vFCPUFDest', 10, 4)->default(0);
            $table->decimal('vICMSUFDest', 10, 4)->default(0);
            $table->decimal('vICMSUFRemet', 10, 4)->default(0);
            $table->decimal('vFCP', 10, 4)->default(0);
            $table->decimal('vBCST', 10, 4)->default(0);
            $table->decimal('vST', 10, 4)->default(0);
            $table->decimal('vFCPST', 10, 4)->default(0);
            $table->decimal('vFCPSTRet', 10, 4)->default(0);
            $table->decimal('vProd', 10, 4)->default(0);
            $table->decimal('vFrete', 10, 4)->default(0);
            $table->decimal('vSeg', 10, 4)->default(0);
            $table->decimal('vDesc', 10, 4)->default(0);
            $table->decimal('vII', 10, 4)->default(0);
            $table->decimal('vIPI', 10, 4)->default(0);
            $table->decimal('vIPIDevol', 10, 4)->default(0);
            $table->decimal('vPIS', 10, 4)->default(0);
            $table->decimal('vCOFINS', 10, 4)->default(0);
            $table->decimal('vOutro', 10, 4)->default(0);
            $table->decimal('vTotTrib', 10, 4)->default(0);
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
