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
        Schema::create('super_logica_cobranca_notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('tenant_id')->nullable()->constrained('tenants')->nullOnDelete();
            $table->foreignId('issuer_id')->nullable()->constrained('issuers')->nullOnDelete();
            $table->integer('id_recebimento_recb')->unsigned();
            $table->integer('id_unidade_uni')->unsigned();
            $table->string('status')->default('pending');
            $table->text('error_message')->nullable();
            $table->json('data')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('super_logica_cobranca_notifications');
    }
};
