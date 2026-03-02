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
            try {
                $table->dropUnique(['num_nfe']);
            } catch (\Throwable) {
            }
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
            $table->unique('num_nfe');
        });
    }
};
