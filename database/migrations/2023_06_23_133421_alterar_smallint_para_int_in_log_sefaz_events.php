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

        Schema::table('log_sefaz_resumo_nfes', function (Blueprint $table) {
            $table->integer('tipo_nfe')->change();
        });

        Schema::table('log_sefaz_res_events', function (Blueprint $table) {
            $table->integer('tipo_evento')->change();
            $table->integer('n_seq_evento')->change();
        });

        Schema::table('log_sefaz_nfe_events', function (Blueprint $table) {
            $table->integer('tp_evento')->change();
            $table->integer('n_seq_evento')->change();
        });

        Schema::table('log_sefaz_cte_events', function (Blueprint $table) {
            $table->integer('tp_evento')->change();
            $table->integer('n_seq_evento')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('log_sefaz_events', function (Blueprint $table) {
            //
        });
    }
};
