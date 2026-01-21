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
        Schema::table('nfses', function (Blueprint $table) {
            $table->string('codigo_servico')->after('prestador_cnpj')->nullable();
            $table->text('descricao_servico')->after('codigo_servico')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nfses', function (Blueprint $table) {
            $table->dropColumn(['codigo_servico', 'descricao_servico']);
        });
    }
};
