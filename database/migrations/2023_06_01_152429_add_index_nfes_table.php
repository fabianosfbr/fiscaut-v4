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
            $table->index('emitente_cnpj');
            $table->index('destinatario_cnpj');
            $table->index('transportador_cnpj');
            $table->index('aut_xml');
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
            $table->dropIndex('emitente_cnpj');
            $table->dropIndex('destinatario_cnpj');
            $table->dropIndex('transportador_cnpj');
            $table->dropIndex('aut_xml');
        });
    }
};
