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
        Schema::create('statistic_issuers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->string('issuer', '50')->index();
            $table->enum('periodo', ['diario', 'semanal', 'mensal', 'anual']);
            $table->enum('doc_tipo', ['nfe', 'cte', 'nfse', 'cfe', 'icms', 'icms_st', 'ipi', 'pis', 'cofins']);
            $table->enum('tipo', ['entrada', 'saida']);
            $table->string('data', '50');
            $table->decimal('valor', 10, 4);
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
        Schema::dropIfExists('statistic_issuers');
    }
};
