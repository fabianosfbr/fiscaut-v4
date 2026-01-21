<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('grupo_entradas_cfops_equivalentes', function (Blueprint $table) {
            $table->unsignedBigInteger('issuer_id')->nullable()->after('tenant_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('grupo_entradas_cfops_equivalentes', function (Blueprint $table) {
            $table->dropColumn('issuer_id');
        });
    }
};
