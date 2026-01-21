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
        Schema::create('issuers', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('cnpj');
            $table->string('inscricao_municipal')->nullable();
            $table->string('razao_social');
            $table->integer('cod_municipio_ibge')->default(3525904);
            $table->integer('ultimo_numero_nfse')->nullable()->default(0);
            $table->integer('ultimo_numero_nfe')->nullable()->default(0);
            $table->integer('ult_nsu_nfe')->nullable()->default(0);
            $table->integer('ult_nsu_cte')->nullable()->default(0);
            $table->integer('maxNSU')->nullable()->default(0);
            $table->dateTime('ultima_consulta_nfe')->nullable();
            $table->integer('ambiente')->default(2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('issuers');
    }
};
