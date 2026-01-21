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
        Schema::table('entradas_acumuladores_equivalentes', function (Blueprint $table) {
            $table->dropColumn('valores');
            // $table->json('valores');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('entradas_acumuladores_equivalentes', function (Blueprint $table) {

            $table->string('valores');
        });
    }
};
