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
        Schema::create('super_logica_plano_de_contas', function (Blueprint $table) {
            $table->id();
            $table->integer('id_condominio')->index();
            $table->string('st_conta_cont')->index();
            $table->string('st_descricao_cont');
            $table->string('st_ordenacao_cont');
            $table->json('metadados')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('super_logica_plano_de_contas');
    }
};
