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
        Schema::create('super_logica_fornecedores', function (Blueprint $table) {
            $table->id();
            $table->integer('id_contato_con')->unsigned();
            $table->integer('id_condominio')->unsigned();
            $table->string('st_nome_con');
            $table->string('st_cpf_con', 20)->nullable();
            $table->json('metadados')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('super_logica_fornecedores');
    }
};
