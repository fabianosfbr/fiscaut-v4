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
        Schema::table('issuers', function (Blueprint $table) {
            $table->string('nome_responsavel')->nullable()->after('razao_social');
            $table->string('email_responsavel')->nullable()->after('nome_responsavel');
            $table->integer('ultimo_numero_cte')->nullable()->after('ultimo_numero_nfe');
            $table->date('ultima_consulta_cte')->nullable()->after('ultima_consulta_nfe');
            $table->date('ultima_consulta_nfse')->nullable()->after('ultima_consulta_cte');
            $table->boolean('nfe_servico')->nullable()->after('ambiente');
            $table->boolean('cte_servico')->nullable()->after('nfe_servico');
            $table->boolean('nfse_servico')->nullable()->after('cte_servico');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('issuers', function (Blueprint $table) {
            //
        });
    }
};
