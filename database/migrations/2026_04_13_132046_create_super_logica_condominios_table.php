<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('super_logica_condominios', function (Blueprint $table) {
            $table->id();
            $table->integer('id_condominio_cond')->index();
            $table->string('st_cpf_cond', 20)->index();
            $table->json('metadados')->nullable();

            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('super_logica_condominios');
    }
};
