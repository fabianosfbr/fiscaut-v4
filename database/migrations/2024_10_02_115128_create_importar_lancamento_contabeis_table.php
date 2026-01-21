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
        Schema::create('contabil_importar_lancamento_contabeis', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('issuer_id');
            $table->unsignedBigInteger('banco_id');
            $table->unsignedBigInteger('user_id');
            $table->decimal('valor', 16, 2)->nullable();
            $table->decimal('saldo', 16, 2)->nullable();
            $table->string('lancamento');
            $table->string('data')->nullable();
            $table->longText('observacao')->nullable();
            $table->boolean('is_exist')->default(false);
            $table->json('conta_contabil')->nullable();
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
