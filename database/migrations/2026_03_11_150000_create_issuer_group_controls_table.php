<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('issuer_group_controls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('issuer_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->unsignedInteger('order')->default(0);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['issuer_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('issuer_group_controls');
    }
};
