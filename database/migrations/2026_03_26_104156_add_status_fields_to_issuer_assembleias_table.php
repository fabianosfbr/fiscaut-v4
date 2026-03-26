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
        Schema::table('issuer_assembleias', function (Blueprint $table) {
            $table->string('assembleia_status')->default('rascunho')->after('type');
            $table->string('ata_status')->default('nao_iniciada')->after('assembleia_status');
            $table->string('deliberacao_status')->default('pendente')->after('ata_status');
            $table->date('data_realizacao')->nullable()->after('deliberacao_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('issuer_assembleias', function (Blueprint $table) {
            $table->dropColumn(['assembleia_status', 'ata_status', 'deliberacao_status', 'data_realizacao']);
        });
    }
};
