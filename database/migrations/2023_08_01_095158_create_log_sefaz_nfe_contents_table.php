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
        Schema::create('log_sefaz_nfe_contents', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('log_event_id');
            $table->foreign('log_event_id')->references('id')->on('log_sefaz_event');
            $table->unsignedBigInteger('issuer_id');
            $table->foreign('issuer_id')->references('id')->on('issuers')->onDelete('cascade');
            $table->integer('nsu')->nullable();
            $table->integer('max_nsu')->nullable();
            $table->longText('xml')->nullable();
            $table->boolean('is_verificado_sefaz')->default(false);
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
        Schema::dropIfExists('log_sefaz_nfe_contents');
    }
};
