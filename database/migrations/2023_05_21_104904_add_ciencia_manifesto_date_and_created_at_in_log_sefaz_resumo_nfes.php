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
            $table->dateTime('data_ciencia_manifesto')->nullable();
            $table->dateTime('created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('log_sefaz_resumo_nfes', function (Blueprint $table) {
            $table->dropColumn('data_ciencia_manifesto');
            $table->dropColumn('created_at');
        });
    }
};
