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
        Schema::create('issuer_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->unsignedBigInteger('issuer_id');
            $table->foreign('issuer_id')->references('id')->on('issuers')->onDelete('cascade');
            $table->string('document_type');
            $table->string('original_name');
            $table->string('user_name');
            $table->string('format');
            $table->string('extension');
            $table->string('file_path');
            $table->integer('file_size');
            $table->string('mime_type');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for better performance
            $table->index(['tenant_id', 'issuer_id']);
            $table->index('document_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('issuer_documents');
    }
};