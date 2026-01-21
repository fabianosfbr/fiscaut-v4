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
        Schema::dropIfExists('contabil_layouts_arquivo_concilicacao');

        Schema::create('contabil_layouts_arquivo_concilicacao', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade')->onUpdate('restrict');
            $table->foreignId('issuer_id')->constrained()->onDelete('cascade')->onUpdate('restrict');
            $table->string('layout')->nullable();
            $table->json('form')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contabil_layouts_arquivo_concilicacao');
    }
};
