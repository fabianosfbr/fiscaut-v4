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
        Schema::table('entradas_cfops_equivalentes', function (Blueprint $table) {
            $table->json('valores')->nullable(true)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('entradas_cfops_equivalentes', function (Blueprint $table) {
            $table->json('valores')->nullable(false)->change();
        });
    }
};
