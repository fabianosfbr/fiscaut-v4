<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ncm_restricoes', function (Blueprint $table) {
            $table->id();
            $table->string('grupo', 20);           // ex: "110", "MON-001", "ISE-001"
            $table->string('descricao', 100);       // ex: "Leite e creme de leite"
            $table->string('tipo', 30);             // ALIQUOTA_ZERO | MONOFASICO | SUSPENSAO | ISENCAO
            $table->string('tipo_match', 20);       // exato | prefixo | capitulo | faixa_prefixo
            $table->json('valor_match');            // array de strings ou tuplas
            $table->string('fundamento', 200)->nullable();
            $table->json('setores')->nullable();
            $table->json('excluir_ncm')->nullable();
            $table->boolean('possui_ex')->default(false);
            $table->text('obs')->nullable();
            $table->timestamps();

            $table->index('grupo');
            $table->index('tipo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ncm_restricoes');
    }
};
