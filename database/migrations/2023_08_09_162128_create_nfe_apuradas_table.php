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
        Schema::create('nfe_apuradas', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('nfe_id');
            $table->foreign('nfe_id')->references('id')->on('nfes')->onDelete('cascade');

            $table->unsignedBigInteger('issuer_id');
            $table->foreign('issuer_id')->references('id')->on('issuers')->onDelete('cascade');

            $table->boolean('status');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('nfe_apuradas');
    }
};
