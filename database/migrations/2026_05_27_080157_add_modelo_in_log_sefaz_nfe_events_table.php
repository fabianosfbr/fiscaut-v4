<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('log_sefaz_nfe_events', function (Blueprint $table) {
            $table->integer('modelo')->default(55)->after('x_evento');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('log_sefaz_nfe_events', function (Blueprint $table) {
            $table->dropColumn('modelo');
        });
    }
};
