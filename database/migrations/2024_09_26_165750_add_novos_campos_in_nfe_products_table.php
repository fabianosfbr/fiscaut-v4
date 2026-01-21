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

            $table->string('cst_icms', 10)->nullable();
            $table->decimal('base_pis', 14, 4)->nullable();
            $table->decimal('valor_pis', 14, 4)->nullable();
            $table->decimal('aliq_pis', 14, 4)->nullable();
            $table->string('cst_pis', 10)->nullable();
            $table->decimal('base_cofins', 14, 4)->nullable();
            $table->decimal('valor_cofins', 14, 4)->nullable();
            $table->decimal('aliq_cofins', 14, 4)->nullable();
            $table->string('cst_cofins', 10)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nfe_products', function (Blueprint $table) {

            $table->dropColumn(['cst_icms',  'base_pis', 'valor_pis', 'aliq_pis',  'cst_pis', 'base_cofins', 'valor_cofins', 'aliq_cofins', 'cst_cofins']);
        });
    }
};
