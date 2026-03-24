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
        Schema::create('issuer_control_event_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('issuer_control_id')->constrained('issuer_controls')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('acao', 50); // criacao, inicio, conclusao, cancelamento, reagendamento, comentario
            $table->string('status_anterior', 50)->nullable();
            $table->string('status_novo', 50)->nullable();
            $table->text('observacao')->nullable();
            $table->json('dados_alterados')->nullable(); // Para armazenar quais campos foram alterados
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('issuer_control_event_logs');
    }
};
