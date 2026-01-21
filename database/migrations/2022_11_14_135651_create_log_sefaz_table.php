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
        Schema::create('log_sefaz', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('issuer_id');
            $table->foreign('issuer_id')->references('id')->on('issuers')->onDelete('cascade');
            $table->integer('nsu')->nullable();
            $table->longText('xml')->nullable();
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
        Schema::dropIfExists('log_sefaz');
    }
};
