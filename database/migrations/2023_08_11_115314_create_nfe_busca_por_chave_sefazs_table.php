<?php

use App\Models\NfeBuscaPorChaveSefaz;
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
        Schema::create('nfe_busca_por_chave_sefaz', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');

            $table->unsignedBigInteger('issuer_id');
            $table->foreign('issuer_id')->references('id')->on('issuers')->onDelete('cascade');

            $table->string('chave');

            $table->enum('status', NfeBuscaPorChaveSefaz::$statusType)->default('pendente');

            $table->dateTime('ultima_consulta')->nullable();

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
        Schema::dropIfExists('nfe_busca_por_chave_sefaz');
    }
};
