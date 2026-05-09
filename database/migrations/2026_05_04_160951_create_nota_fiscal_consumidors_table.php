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
        if (! Schema::hasTable('nfces')) {
            Schema::create('nfces', function (Blueprint $table) {
                $table->uuid('id')->primary()->unique();
                $table->string('chave')->nullable();
                $table->date('data_emissao')->nullable();
                $table->integer('mod')->nullable();
                $table->string('emitente_razao_social')->nullable();
                $table->string('emitente_ie')->nullable();
                $table->string('emitente_cnpj')->nullable();
                $table->string('nProt')->nullable();
                $table->string('nNF')->nullable();
                $table->string('origem')->nullable();
                $table->integer('status_nota')->nullable();
                $table->decimal('vNfe', 10, 4)->nullable();
                $table->integer('tpNf')->nullable();
                $table->binary('xml')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nfces');
    }
};
