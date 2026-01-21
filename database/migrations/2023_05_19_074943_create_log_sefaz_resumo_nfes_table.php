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
        Schema::create('log_sefaz_resumo_nfes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('chave')->index();
            $table->string('cnpj')->index();
            $table->string('razao_social');
            $table->string('iscricao_estadual')->nullable();
            $table->dateTime('dh_emissao');
            $table->smallInteger('tipo_nfe');
            $table->decimal('valor_nfe', 10, 4);
            $table->boolean('is_ciente_operacao')->default(false);
            $table->longText('xml');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('log_sefaz_resumo_nfes');
    }
};
