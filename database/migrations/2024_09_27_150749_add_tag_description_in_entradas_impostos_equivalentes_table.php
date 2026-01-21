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
        Schema::table('entradas_impostos_equivalentes', function (Blueprint $table) {
            $table->string('tag_description')->nullable()->after('tag');
            $table->bigInteger('tag_id')->nullable()->after('tag_description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('entradas_impostos_equivalentes', function (Blueprint $table) {
            $table->dropColumn('tag_description', 'tag_id');
        });
    }
};
