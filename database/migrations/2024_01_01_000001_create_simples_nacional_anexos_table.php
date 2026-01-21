<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('simples_nacional_anexos', function (Blueprint $table) {
            $table->id();
            $table->string('anexo', 5)->unique();
            $table->text('descricao');
            $table->boolean('ativo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('simples_nacional_anexos');
    }
};
