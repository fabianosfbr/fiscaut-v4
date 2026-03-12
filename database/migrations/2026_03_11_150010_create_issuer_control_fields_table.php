<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('issuer_control_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('issuer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('issuer_group_control_id')
                ->nullable()
                ->constrained('issuer_group_controls')
                ->nullOnDelete();

            $table->string('key');
            $table->string('label');
            $table->string('type');
            $table->string('attribute')->nullable();
            $table->text('description')->nullable();
            $table->boolean('required')->default(false);
            $table->unsignedInteger('order')->default(0);
            $table->json('options')->nullable();
            $table->json('accepted_types')->nullable();

            $table->timestamps();

            $table->unique(['issuer_id', 'key']);
            $table->index(['issuer_id', 'issuer_group_control_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('issuer_control_fields');
    }
};
