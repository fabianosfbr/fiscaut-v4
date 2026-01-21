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
        Schema::table('entradas_cfops_equivalentes', function (Blueprint $table) {

            $table->unsignedBigInteger('grupo_id')->nullable();
            //$table->foreign('grupo_id')->references('id')->on('grupo_entradas_cfops_equivalentes')->onDelete('cascade')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('entradas_cfops_equivalentes', function (Blueprint $table) {

        });
    }
};
