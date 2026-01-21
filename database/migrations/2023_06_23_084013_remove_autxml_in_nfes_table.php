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

            $table->dropIndex(['aut_xml']);
            $table->dropColumn('aut_xml');
            //  $table->json('aut_xml')->after('transportador_razao_social')->nullable();
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
            //  $table->index('aut_xml');
        });
    }
};
