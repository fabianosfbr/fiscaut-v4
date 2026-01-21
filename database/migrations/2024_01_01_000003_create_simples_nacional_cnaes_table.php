<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('simples_nacional_cnaes', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_cnae', 10)->unique();
            $table->text('descricao');
            $table->string('anexo', 5);
            $table->boolean('ativo')->default(true);
            $table->timestamps();

            $table->foreign('anexo')->references('anexo')->on('simples_nacional_anexos');
            $table->index('codigo_cnae');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('simples_nacional_cnaes');
    }
};
