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
        Schema::table('issuers', function (Blueprint $table) {
            $table->boolean('sync_sefaz')->nullable()->default(false);
            $table->boolean('sync_sieg')->nullable()->default(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('issuers', function (Blueprint $table) {
            $table->dropColumn('sync_sefaz');
            $table->dropColumn('sync_sieg');
        });
    }
};
