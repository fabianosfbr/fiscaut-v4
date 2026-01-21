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
        Schema::table('log_sefaz_res_events', function (Blueprint $table) {
            $table->bigInteger('issuer_id');
            $table->boolean('is_verificado_sefaz')->default(false);
        });

        Schema::table('log_sefaz_cte_events', function (Blueprint $table) {
            $table->bigInteger('issuer_id');
            $table->boolean('is_verificado_sefaz')->default(false);
        });

        Schema::table('log_sefaz_nfe_events', function (Blueprint $table) {
            $table->bigInteger('issuer_id');
            $table->boolean('is_verificado_sefaz')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('log_sefaz_res_events', function (Blueprint $table) {
            $table->dropColumn('issuer_id');
            $table->dropColumn('is_verificado_sefaz');
        });

        Schema::table('log_sefaz_cte_events', function (Blueprint $table) {
            $table->dropColumn('issuer_id');
            $table->dropColumn('is_verificado_sefaz');
        });

        Schema::table('log_sefaz_nfe_events', function (Blueprint $table) {
            $table->dropColumn('issuer_id');
            $table->dropColumn('is_verificado_sefaz');
        });
    }
};
