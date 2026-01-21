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
            $table->boolean('is_sanitize')->default(false)->after('format');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contabil_layout_columns', function (Blueprint $table) {
            $table->dropColumn('is_sanitize');
        });
    }
};
