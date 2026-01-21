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
        Schema::table('ctes', function (Blueprint $table) {
            $table->integer('tpCTe')->after('nCTe')->nullable;
            $table->string('nfe_chave')->after('xMunFim')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ctes', function (Blueprint $table) {
            //
        });
    }
};
