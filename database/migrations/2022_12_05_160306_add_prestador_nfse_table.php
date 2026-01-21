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
        Schema::table('nfses', function (Blueprint $table) {
            $table->string('prestador_servico')->after('tomador_cnpj')->nullable();
            $table->string('prestador_cnpj')->after('prestador_servico')->nullable()->index();
            $table->string('prestador_im')->after('prestador_cnpj')->nullable();
            $table->string('origem')->after('cancelada')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('nfses', function (Blueprint $table) {
            //
        });
    }
};
