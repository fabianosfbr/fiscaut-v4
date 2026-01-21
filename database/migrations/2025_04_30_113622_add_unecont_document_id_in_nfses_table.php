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
        Schema::table('nfses', function (Blueprint $table) {
            $table->bigInteger('unecont_document_id')->nullable()->index();
            $table->bigInteger('substituido_id')->nullable()->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nfses', function (Blueprint $table) {
            $table->dropColumn('unecont_document_id');
            $table->dropColumn('substituido_id');
        });
    }
};
