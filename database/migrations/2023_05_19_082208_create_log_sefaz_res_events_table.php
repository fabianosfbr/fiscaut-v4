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
        Schema::create('log_sefaz_res_events', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->smallInteger('orgao')->nullable();
            $table->string('chave')->index();
            $table->string('cnpj')->index();
            $table->dateTime('dh_evento')->nullable();
            $table->smallInteger('tipo_evento')->nullable();
            $table->smallInteger('n_seq_evento')->nullable();
            $table->string('evento')->nullable();
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
        Schema::dropIfExists('log_sefaz_res_events');
    }
};
