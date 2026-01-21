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
        Schema::create('contabil_layouts_arquivo_concilicacao', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('tenant_id')->unsigned();
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->bigInteger('issuer_id')->unsigned();
            $table->foreign('issuer_id')->references('id')->on('issuers')->onDelete('cascade');
            $table->string('name');
            $table->string('slug');
            $table->string('class');
            $table->string('path_class');
            $table->boolean('is_current')->default(false);
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
