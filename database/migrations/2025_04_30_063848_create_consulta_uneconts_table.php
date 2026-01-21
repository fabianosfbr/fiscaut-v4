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
        Schema::create('consultas_unecont', function (Blueprint $table) {
            $table->id();
            $table->foreignId('issuer_id')->constrained('issuers');
            $table->integer('ultimo_evento_id')->default(0);
            $table->integer('maximo_evento_id')->default(0);
            $table->integer('num_documentos')->default(0);
            $table->dateTime('data_hora_proxima_requisicao')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consultas_unecont');
    }
};
