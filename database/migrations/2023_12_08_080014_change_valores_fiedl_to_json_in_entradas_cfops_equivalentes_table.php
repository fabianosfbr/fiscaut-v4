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
        Schema::table('entradas_cfops_equivalentes', function (Blueprint $table) {

            if (Schema::hasColumn($table, 'valores')) {

                $table->dropColumn('valores');
            }
        });
        Schema::table('entradas_cfops_equivalentes', function (Blueprint $table) {

            $table->integer('cfop_entrada')->nullable()->change();
            $table->json('valores')->nullable()->after('cfop_entrada');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('entradas_cfops_equivalentes', function (Blueprint $table) {
            $table->dropColumn(['valores']);
        });

        Schema::table('entradas_cfops_equivalentes', function (Blueprint $table) {
            $table->string('valores')->nullable()->after('cfop_entrada');
        });
    }
};
