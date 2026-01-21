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
        Schema::create('nfes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('issuer_id');
            $table->foreign('issuer_id')->references('id')->on('issuers')->onDelete('cascade');
            $table->integer('nsu')->nullable();
            $table->string('tipo_retorno')->nullable();
            $table->string('chave')->nullable();
            $table->string('emitente_razao_social')->nullable();
            $table->string('emitente_ie')->nullable();
            $table->string('emitente_cnpj')->nullable();
            $table->date('data_emissao')->nullable();
            $table->integer('status_manifestacao')->nullable();
            $table->string('nProt')->nullable();
            $table->string('nNF')->nullable();
            $table->string('origem')->nullable();
            $table->integer('status_nota')->nullable();
            $table->decimal('vNfe', 10, 4)->nullable();
            $table->integer('tpNf')->nullable();
            $table->integer('sitNfe')->nullable();
            $table->string('digVal')->nullable();
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
        Schema::dropIfExists('nfes');
    }
};
