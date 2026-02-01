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
        Schema::create('log_sefaz_nfse_events', function (Blueprint $table) {
            $table->id();
            $table->string('chave')->index();
            $table->datetime('dh_evento');
            $table->string('x_desc')->nullable();
            $table->string('c_motivo')->nullable();
            $table->string('x_motivo')->nullable();
            $table->string('ch_substituta')->nullable();
            $table->longText('xml');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('log_sefaz_nfse_events');
    }
};
