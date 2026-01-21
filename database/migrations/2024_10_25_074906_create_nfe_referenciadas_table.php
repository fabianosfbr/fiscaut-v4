<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('nfe_referenciadas', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('nfe_referenciada');
            $table->unsignedBigInteger('nfe_id');
            $table->foreign('nfe_id')->references('id')->on('nfes')->onDelete('cascade');
            $table->foreign('nfe_referenciada')->references('id')->on('nfes')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nfe_referenciadas');
    }
};
