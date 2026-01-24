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
            $table->boolean('is_difal')->default(false)->after('color');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories_tag', function (Blueprint $table) {
            $table->dropColumn('is_difal');
        });
    }
};
//2024_01_18_163956_add_is_devolucao_in_categories_tag_table
