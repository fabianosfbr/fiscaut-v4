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
            $table->date('data_abertura')->nullable();
            $table->string('telefone')->nullable();
            $table->string('email')->nullable();

            // Endereço
            $table->string('logradouro')->nullable();
            $table->string('numero')->nullable();
            $table->string('complemento')->nullable();
            $table->string('bairro')->nullable();
            $table->string('cidade')->nullable();
            $table->string('uf')->nullable();
            $table->string('cep')->nullable();

            // Situação cadastral
            $table->string('situacao_cadastral')->nullable();
            $table->date('data_situacao_cadastral')->nullable();

            // Atividades econômicas
            $table->json('main_activity')->nullable();

            // Adicionar campos para atividades secundárias
            $table->json('side_activities')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('issuers', function (Blueprint $table) {
            $table->dropColumn([
                'data_abertura',
                'telefone',
                'email',
                'logradouro',
                'numero',
                'complemento',
                'bairro',
                'cep',
                'situacao_cadastral',
                'data_situacao_cadastral',
                'main_activity',
                'side_activities',
            ]);
        });
    }
};
