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
        Schema::table('entradas_produtos_genericos', function (Blueprint $table) {

            $table->unsignedBigInteger('grupo_id')->nullable();

            $table->dropForeign('entradas_produtos_genericos_config_global_id_foreign');
            $table->dropColumn('config_global_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('entradas_produtos_genericos', function (Blueprint $table) {
            $table->unsignedBigInteger('grupo_id')->nullable();
        });
    }
};
