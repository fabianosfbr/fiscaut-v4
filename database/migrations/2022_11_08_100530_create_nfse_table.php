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
        Schema::create('nfses', function (Blueprint $table) {
            $table->id();
            $table->integer('numero')->nullable();
            $table->string('codigo_verificacao')->nullable();
            $table->date('data_emissao')->nullable();
            $table->decimal('valor_servico', 10, 4)->nullable();
            $table->string('tomador_servico')->nullable()->index();
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
        Schema::dropIfExists('nfse');
    }
};
