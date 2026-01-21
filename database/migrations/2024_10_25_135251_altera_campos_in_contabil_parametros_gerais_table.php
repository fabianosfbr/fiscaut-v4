<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('contabil_parametros_gerais', function (Blueprint $table) {
            $table->integer('codigo_historico');
            $table->json('complemento_historico')->change();

            $table->dropColumn('hp');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contabil_parametros_gerais', function (Blueprint $table) {
            $table->dropColumn('codigo_historico');

            $table->string('hp')->nullable();
            $table->string('complemento_historico')->change();
        });
    }
};
