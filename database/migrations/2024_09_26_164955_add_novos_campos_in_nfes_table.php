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
            $table->integer('num_produtos')->nullable();
            $table->json('cfops')->nullable();
            $table->json('cobranca')->nullable();
            $table->json('pagamento')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nfes', function (Blueprint $table) {
            $table->dropColumn('num_produtos', 'cfops', 'cobranca', 'pagamento');
        });
    }
};
