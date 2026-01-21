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
        Schema::table('codigos_servico', function (Blueprint $table) {

            $table->foreignId('cnae_id')->nullable()->constrained('cnaes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('codigos_servico', function (Blueprint $table) {
            $table->dropForeign(['cnae_id']);
            $table->dropColumn('cnae_id');
        });
    }
};
