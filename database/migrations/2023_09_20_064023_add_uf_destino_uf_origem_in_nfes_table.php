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
            $table->string('enderEmit_UF', 5)->nullable()->after('emitente_cnpj');
            $table->string('enderDest_UF', 5)->nullable()->after('destinatario_razao_social');
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
            $table->dropColumn('enderEmit_UF');
            $table->dropColumn('enderDest_UF');
        });
    }
};
