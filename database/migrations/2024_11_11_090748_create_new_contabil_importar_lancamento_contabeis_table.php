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
            $table->id();
            $table->bigInteger('issuer_id');
            $table->bigInteger('banco_id');
            $table->bigInteger('user_id');
            $table->bigInteger('layout_id')->nullable();
            $table->decimal('valor', 16, 2)->nullable();
            $table->decimal('saldo', 16, 2)->nullable();
            $table->string('lancamento', 255)->nullable();
            $table->string('data', 255)->nullable();
            $table->longText('observacao')->nullable();
            $table->boolean('is_exist')->default(0);

            $table->json('metadata')->nullable();
            $table->integer('debito')->nullable()->default(0);
            $table->integer('credito')->nullable()->default(0);
            $table->string('descricao_debito', 255)->nullable();
            $table->string('descricao_credito', 255)->nullable();

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
