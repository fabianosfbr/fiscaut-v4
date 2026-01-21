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
        Schema::create('nfe_produts', function (Blueprint $table) {
            $table->bigIncrements('id')->index();
            $table->unsignedBigInteger('nfe_id');
            $table->foreign('nfe_id')->references('id')->on('nfes')->onDelete('cascade');
            $table->string('codigo_produto');
            $table->string('descricao_produto');
            $table->string('cst')->nullable();
            $table->string('ncm');
            $table->integer('cfop');
            $table->string('unidade');
            $table->decimal('quantidade', 10, 2);
            $table->decimal('valor_unit', 10, 4);
            $table->decimal('valor_total', 10, 4);
            $table->decimal('valor_desc', 10, 4)->nullable();
            $table->decimal('base_icms', 10, 4);
            $table->decimal('valor_icms', 10, 4);
            $table->decimal('aliq_icms', 10, 4);
            $table->decimal('base_ipi', 10, 4)->nullable();
            $table->decimal('valor_ipi', 10, 4)->nullable();
            $table->decimal('aliq_ipi', 10, 4)->nullable();
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
        Schema::dropIfExists('nfe_produts');
    }
};
