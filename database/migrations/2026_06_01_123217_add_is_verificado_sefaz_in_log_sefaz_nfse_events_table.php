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
        Schema::table('log_sefaz_nfse_events', function (Blueprint $table) {
            $table->boolean('is_verificado_sefaz')->after('ch_substituta')->default(false);
            //
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('log_sefaz_nfse_events', function (Blueprint $table) {
            $table->dropColumn('is_verificado_sefaz');
        });
    }
};
