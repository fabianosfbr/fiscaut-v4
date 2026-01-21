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
        Schema::table('contabil_layout_columns', function (Blueprint $table) {
            $table->string('date_adjustment')->default('same')->after('format'); // Pode ser: 'same', 'd-1', 'd+1'
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contabil_layout_columns', function (Blueprint $table) {
            $table->dropColumn('date_adjustment');
        });
    }
};
