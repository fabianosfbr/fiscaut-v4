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
        Schema::table('ctes', function (Blueprint $table) {
            $table->dropIndex('ctes_nfe_chave_index');
            //  $table->json('nfe_chave')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ctes', function (Blueprint $table) {
            //
        });
    }
};
