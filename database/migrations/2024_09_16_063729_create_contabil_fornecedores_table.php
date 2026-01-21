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
        Schema::create('contabil_fornecedores', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('issuer_id');
            $table->string('nome');
            $table->string('cnpj')->nullable();
            $table->json('conta_contabil')->nullable();
            $table->json('descricao_conta_contabil')->nullable();
            $table->json('colunas_arquivo')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fornecedors');
    }
};
