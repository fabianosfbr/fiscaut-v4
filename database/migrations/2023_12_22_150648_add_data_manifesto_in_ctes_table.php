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
            $table->dateTime('data_manifesto')->nullable()->after('data_emissao');
            $table->integer('status_manifestacao')->default(0)->after('data_manifesto');
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
            $table->dropColumn(['data_manifesto', 'status_manifestacao']);
        });
    }
};
