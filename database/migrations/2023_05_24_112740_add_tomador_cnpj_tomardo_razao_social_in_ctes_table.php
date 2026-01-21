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
            $table->string('tomador_razao_social')->after('remetente_cnpj')->nullable();
            $table->string('tomador_cnpj')->after('tomador_razao_social')->nullable();
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
            $table->dropColumn('tomador_razao_social');
            $table->dropColumn('tomador_cnpj');
        });
    }
};
