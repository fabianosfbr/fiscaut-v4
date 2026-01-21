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
        Schema::table('cfes', function (Blueprint $table) {
            $table->string('emitente_razao_social')->after('chave')->nullable();
            $table->string('emitente_cnpj')->after('emitente_razao_social')->nullable()->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cfes', function (Blueprint $table) {
            //
        });
    }
};
