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
        Schema::table('contabil_bancos', function (Blueprint $table) {
            $table->renameColumn('data_arquivo', 'conta_contabil_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contabil_bancos', function (Blueprint $table) {
            $table->renameColumn('conta_contabil_code', 'data_arquivo');
        });
    }
};
