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
            $table->boolean('processed')->default(false)->after('id');

            $table->dropColumn('nsu');
            $table->dropColumn('tipo_retorno');
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
            $table->dropColumn('processed');

            $table->integer('nsu');
            $table->string('tipo_retorno');
        });
    }
};
