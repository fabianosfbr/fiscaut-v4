<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('upload_file_extractions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('upload_file_id');
            $table->string('cnpj_fornecedor')->nullable();
            $table->string('razao_social_fornecedor')->nullable();
            $table->date('data_emissao')->nullable();
            $table->date('data_vencimento')->nullable();
            $table->string('numero_fatura_contrato')->nullable();
            $table->decimal('valor_documento', 10, 2)->nullable();
            $table->string('cfop_equivalente')->nullable();
            $table->string('acumulador')->nullable();
            $table->json('etiquetas_valores')->nullable();
            $table->json('raw_response')->nullable();
            $table->timestamps();

            $table->index(['cnpj_fornecedor']);
            $table->foreign('upload_file_id')->references('id')->on('upload_files')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('upload_file_extractions');
    }
};
