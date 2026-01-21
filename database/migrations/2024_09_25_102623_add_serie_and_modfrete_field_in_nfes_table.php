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
        Schema::table('nfes', function (Blueprint $table) {
            $table->integer('serie')->nullable();
            $table->integer('modFrete')->nullable();
            $table->string('destinatario_im')->nullable()->after('destinatario_ie');
            $table->string('emitente_im')->nullable()->after('emitente_ie');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nfes', function (Blueprint $table) {
            $table->dropColumn('serie', 'modFrete', 'destinatario_im', 'emitente_im');
        });
    }
};
