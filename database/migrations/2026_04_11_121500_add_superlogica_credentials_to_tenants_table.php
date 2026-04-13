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
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('superlogica_base_url')->nullable()->after('fiscaut_connector_token');
            $table->string('superlogica_app_token')->nullable()->after('superlogica_base_url');
            $table->string('superlogica_access_token')->nullable()->after('superlogica_app_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn([
                'superlogica_base_url',
                'superlogica_app_token',
                'superlogica_access_token',
            ]);
        });
    }
};
