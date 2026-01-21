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
        Schema::table('general_settings', function (Blueprint $table) {
            $table->unsignedBigInteger('issuer_id')->nullable()->after('tenant_id');
        });
        Schema::table('grupo_entradas_produtos_genericos', function (Blueprint $table) {
            $table->unsignedBigInteger('issuer_id')->nullable()->after('tenant_id');
        });

        Schema::table('entradas_impostos_equivalentes', function (Blueprint $table) {
            $table->unsignedBigInteger('issuer_id')->nullable()->after('tenant_id');
        });

        Schema::table('entradas_acumuladores_equivalentes', function (Blueprint $table) {
            $table->unsignedBigInteger('issuer_id')->nullable()->after('tenant_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('general_settings', function (Blueprint $table) {
            $table->dropColumn('issuer_id');
        });
        Schema::table('grupo_entradas_produtos_genericos', function (Blueprint $table) {
            $table->dropColumn('issuer_id');
        });

        Schema::table('entradas_impostos_equivalentes', function (Blueprint $table) {
            $table->dropColumn('issuer_id');
        });

        Schema::table('entradas_acumuladores_equivalentes', function (Blueprint $table) {
            $table->dropColumn('issuer_id');
        });
    }
};
