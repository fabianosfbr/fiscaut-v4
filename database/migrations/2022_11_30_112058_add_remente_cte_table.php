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
            $table->string('remetente_razao_social')->nullable()->after('tomador_cnpj');
            $table->string('remetente_cnpj')->nullable()->index()->after('rementente_razao_social');
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
