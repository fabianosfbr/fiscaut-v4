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
        Schema::table('nfes', function (Blueprint $table) {
            $table->string('transportador_cnpj')->after('destinatario_razao_social')->nullable();
            $table->string('transportador_razao_social')->after('transportador_cnpj')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('nfes', function (Blueprint $table) {
            //
        });
    }
};
