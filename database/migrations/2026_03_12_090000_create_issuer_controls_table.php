<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('issuer_controls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('issuer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('issuer_control_field_id')->constrained('issuer_control_fields')->cascadeOnDelete();
            $table->json('value')->nullable();
            $table->timestamps();

            $table->unique(['issuer_id', 'issuer_control_field_id']);
            $table->index(['issuer_id', 'issuer_control_field_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('issuer_controls');
    }
};
