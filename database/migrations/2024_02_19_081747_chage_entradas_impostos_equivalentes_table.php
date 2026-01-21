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
        Schema::table('entradas_impostos_equivalentes', function (Blueprint $table) {

            $table->boolean('status_icms')->default(true)->after('description');
            $table->boolean('status_ipi')->default(true)->after('status_icms');

            $table->dropColumn('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('entradas_impostos_equivalentes', function (Blueprint $table) {

            $table->boolean('status')->default(true)->after('description');
            $table->dropColumn(['status_icms', 'status_ipi']);

        });
    }
};
