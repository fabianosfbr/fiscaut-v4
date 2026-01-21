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
        Schema::create('plano_de_contas', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('parent_id')->nullable();
            $table->bigInteger('issuer_id')->nullable();
            $table->bigInteger('tenant_id')->nullable();
            $table->string('conta', 100);
            $table->string('codigo', 25);
            $table->string('tipo', 1)->nullable();
            $table->string('alias', 50)->nullable();
            $table->string('natureza')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plano_de_contas');
    }
};
