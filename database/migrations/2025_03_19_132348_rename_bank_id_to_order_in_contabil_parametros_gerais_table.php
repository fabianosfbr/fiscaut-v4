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
        Schema::table('contabil_parametros_gerais', function (Blueprint $table) {
            $table->renameColumn('banco_id', 'order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contabil_parametros_gerais', function (Blueprint $table) {
            $table->renameColumn('order', 'banco_id');
        });
    }
};
