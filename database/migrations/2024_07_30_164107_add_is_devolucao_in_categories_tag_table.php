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
        Schema::table('categories_tag', function (Blueprint $table) {
            $table->boolean('is_devolucao')->default(false)->after('is_difal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories_tag', function (Blueprint $table) {
            $table->dropColumn('is_devolucao');
        });
    }
};
