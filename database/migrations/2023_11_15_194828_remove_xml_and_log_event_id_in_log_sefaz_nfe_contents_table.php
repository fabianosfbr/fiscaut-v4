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
        Schema::table('log_sefaz_nfe_contents', function (Blueprint $table) {
            $table->dropForeign('log_sefaz_nfe_contents_issuer_id_foreign');
            $table->dropForeign('log_sefaz_nfe_contents_log_event_id_foreign');
            $table->dropColumn(['xml', 'log_event_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('log_sefaz_nfe_contents', function (Blueprint $table) {
            //
        });
    }
};
