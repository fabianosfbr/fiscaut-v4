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
        Schema::table('nfe_products', function (Blueprint $table) {
            $table->string('num_nfe')->unique()->nullable()->after('nfe_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('nfe_products', function (Blueprint $table) {
            $table->dropColumn('num_nfe');
        });
    }
};
