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
            $table->integer('csosn')->nullable();
            $table->decimal('valor_total_trib', 10, 4)->nullable();
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
            $table->dropColumn('csosn');
            $table->dropColumn('valor_total_trib');
        });
    }
};
