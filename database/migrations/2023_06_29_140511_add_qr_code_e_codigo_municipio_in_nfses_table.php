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
            $table->integer('qr_code')->nullable();
            $table->integer('codigo_municipio')->nullable();
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
            $table->dropColumn('qr_code');
            $table->dropColumn('codigo_municipio');
        });
    }
};
