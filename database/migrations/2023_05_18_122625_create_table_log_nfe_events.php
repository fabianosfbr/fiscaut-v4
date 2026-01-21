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
        Schema::create('log_sefaz_nfe_events', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('chave')->index();
            $table->smallInteger('tp_evento')->index();
            $table->smallInteger('n_seq_evento');
            $table->dateTime('dh_evento');
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
        Schema::dropIfExists('log_sefaz_nfe_events');
    }
};
