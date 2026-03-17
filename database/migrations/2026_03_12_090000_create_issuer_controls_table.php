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
            $table->string('control_type');
            $table->json('value')->nullable();
            $table->timestamps();
            
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('issuer_controls');
    }
};
