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
        Schema::table('super_logica_condominios', function (Blueprint $table) {
            $table->bigInteger('super_logica_condominio_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('super_logica_condominios', function (Blueprint $table) {
            $table->dropColumn('super_logica_condominio_id');
        });
    }
};
