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
        Schema::dropIfExists('contabil_importar_lancamento_contabeis');

        Schema::create('contabil_importar_lancamento_contabeis', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('issuer_id')->constrained();
            $table->foreignId('user_id')->constrained();
            $table->string('data')->nullable();
            $table->decimal('valor', 16, 2)->nullable();
            $table->longText('historico')->nullable();
            $table->integer('debito')->nullable();
            $table->integer('credito')->nullable();
            $table->boolean('is_exist')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contabil_importar_lancamento_contabeis');
    }
};
