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
        Schema::table('contabil_bancos', function (Blueprint $table) {
            $table->string('date_adjustment')->default('same')->after('issuer_id'); // Valores: 'same', 'd-1', 'd+1'
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contabil_bancos', function (Blueprint $table) {
            $table->dropColumn('date_adjustment');
        });
    }
};
