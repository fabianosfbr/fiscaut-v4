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
        Schema::create('issuer_assembleia_event_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('issuer_assembleia_id')->constrained('issuer_assembleias')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('acao')->default('alteracao');
            $table->string('campo_alterado')->nullable();
            $table->text('valor_anterior')->nullable();
            $table->text('valor_novo')->nullable();
            $table->text('observacao')->nullable();
            $table->json('dados_alterados')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('issuer_assembleia_event_logs');
    }
};
