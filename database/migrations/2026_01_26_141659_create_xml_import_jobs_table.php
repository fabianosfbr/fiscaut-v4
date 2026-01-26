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
        Schema::create('xml_import_jobs', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id')->nullable()->index();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('issuer_id')->nullable()->constrained()->onDelete('set null');
            $table->string('status')->default('pending');
            $table->unsignedInteger('total_files')->default(0);
            $table->integer('num_documents')->default(0);
            $table->integer('num_events')->default(0);
            $table->integer('processed_files')->default(0);
            $table->integer('imported_files')->default(0);
            $table->integer('error_files')->default(0);
            $table->json('errors')->nullable();
            $table->string('batch_id')->nullable();

            $table->unsignedBigInteger('owner_id')->nullable();
            $table->string('owner_type')->nullable();

            $table->string('import_type')->default('user');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('xml_import_jobs');
    }
};
