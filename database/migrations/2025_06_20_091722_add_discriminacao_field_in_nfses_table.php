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
            $table->text('discriminacao')->nullable()->after('prestador_im');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nfses', function (Blueprint $table) {
            $table->dropColumn('discriminacao');
        });
    }
};
