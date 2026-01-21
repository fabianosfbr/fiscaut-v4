<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('log_sefaz_manifesto_event', function (Blueprint $table) {
            $table->longText('xml')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('log_sefaz_manifesto_event', function (Blueprint $table) {
            $table->string('xml')->nullable()->change();
        });
    }
};
