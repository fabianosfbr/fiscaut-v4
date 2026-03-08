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
        Schema::table('issuers', function (Blueprint $table) {
            $table->integer('natureza_operacao_id')->after('razao_social')->nullable();
            $table->string('natureza_operacao')->after('natureza_operacao_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('issuers', function (Blueprint $table) {
            $table->dropColumn('natureza_operacao_id');
            $table->dropColumn('natureza_operacao');
        });
    }
};
