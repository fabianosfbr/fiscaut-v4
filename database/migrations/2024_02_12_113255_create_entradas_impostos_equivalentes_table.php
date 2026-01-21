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
        Schema::create('entradas_impostos_equivalentes', function (Blueprint $table) {
            $table->id();
            $table->integer('tag');
            $table->string('description');
            $table->boolean('status');
            $table->bigInteger('tenant_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('entradas_impostos_equivalentes');
    }
};
