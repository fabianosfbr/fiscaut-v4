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
        Schema::table('issuers', function (Blueprint $table) {
            $table->string('inscricao_estadual')->nullable();
            $table->boolean('contribuinte_icms')->default(true)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('issuers', function (Blueprint $table) {
            $table->dropColumn('inscricao_estadual');
            $table->dropColumn('contribuinte_icms');
        });
    }
};
