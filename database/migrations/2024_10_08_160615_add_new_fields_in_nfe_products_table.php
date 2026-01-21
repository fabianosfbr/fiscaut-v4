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
        Schema::table('nfe_products', function (Blueprint $table) {

            $table->decimal('valor_pis_st', 10, 2)->nullable();
            $table->decimal('vCredICMSSN', 10, 2)->nullable();
            $table->decimal('pCredSN', 10, 2)->nullable();
            $table->decimal('base_st', 10, 2)->nullable();
            $table->decimal('aliq_st', 10, 2)->nullable();
            $table->decimal('valor_st', 10, 2)->nullable();
            $table->decimal('valor_ii', 10, 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nfe_products', function (Blueprint $table) {
            $table->dropColumn('valor_pis_st', 'vCredICMSSN', 'pCredSN', 'base_st', 'aliq_st', 'valor_st', 'valor_ii');
        });
    }
};
