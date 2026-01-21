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
        Schema::table('issuers', function (Blueprint $table) {
            $table->boolean('sync_unecont')->default(false);
            $table->string('login_municipio')->nullable();
            $table->string('senha_municipio')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('issuers', function (Blueprint $table) {
            $table->dropColumn('sync_unecont');
            $table->dropColumn('login_municipio');
            $table->dropColumn('senha_municipio');
        });
    }
};
