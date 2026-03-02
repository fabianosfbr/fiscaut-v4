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
            try {
                $table->dropIndex('emitente_cnpj');
            } catch (\Throwable) {
            }

            try {
                $table->dropIndex('destinatario_cnpj');
            } catch (\Throwable) {
            }

            try {
                $table->dropIndex('transportador_cnpj');
            } catch (\Throwable) {
            }

            try {
                $table->dropIndex('aut_xml');
            } catch (\Throwable) {
            }
        });
    }
};
