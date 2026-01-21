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
        //Create new foreignkey
        Schema::table('categories_tag', function (Blueprint $table) {
            $table->unsignedBigInteger('issuer_id')->index()->nullable();
            //$table->foreign('issuer_id')->references('id')->on('issuers')->onDelete('restrict');
        });

        Schema::table('tagging_tags', function (Blueprint $table) {
            $table->unsignedBigInteger('issuer_id')->index()->nullable();
            //$table->foreign('issuer_id')->references('id')->on('issuers')->onDelete('restrict');
        });

        /*         //Drop old foreignkey
        Schema::table('categories_tag', function (Blueprint $table) {
            $table->dropForeign('user_id');
        });

        Schema::table('tagging_tags', function (Blueprint $table) {
            $table->dropForeign('user_id');
        }); */
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('categories_tag', function (Blueprint $table) {
            //
        });
    }
};
