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
        Schema::create('ctes', function (Blueprint $table) {
            $table->id();
            $table->string('chave')->nullable()->index();
            $table->string('emitente_razao_social')->nullable();
            $table->string('emitente_cnpj')->nullable()->index();
            $table->string('destinatario_razao_social')->nullable();
            $table->string('destinatario_cnpj')->nullable()->index();
            $table->date('data_emissao')->nullable();
            $table->string('nProt')->nullable();
            $table->string('nCTe')->nullable();
            $table->string('origem')->nullable();
            $table->decimal('vCTe', 10, 4)->nullable();
            $table->integer('tpNf')->nullable();
            $table->integer('status_cte')->nullable();
            $table->string('uf_origem')->nullable();
            $table->string('xMunIni')->nullable();
            $table->string('uf_destino')->nullable();
            $table->string('xMunFim')->nullable();
            $table->longText('xml')->nullable();
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
        Schema::dropIfExists('ctes');
    }
};
