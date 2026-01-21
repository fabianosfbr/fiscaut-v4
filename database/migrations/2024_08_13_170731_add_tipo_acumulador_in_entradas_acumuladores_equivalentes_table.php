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
        Schema::table('entradas_acumuladores_equivalentes', function (Blueprint $table) {
            $table->string('tipo')->nullable()->default('nfe-entrada-terceiro')->after('cfops');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('entradas_acumuladores_equivalentes', function (Blueprint $table) {
            $table->dropColumn('tipo');
        });
    }
};
