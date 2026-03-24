<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('issuers', function (Blueprint $table) {
            $table->unsignedInteger('residential_count')->nullable()->after('units_count');
            $table->unsignedInteger('commercial_count')->nullable()->after('residential_count');
        });
    }

    public function down(): void
    {
        Schema::table('issuers', function (Blueprint $table) {
            $table->dropColumn(['residential_count', 'commercial_count']);
        });
    }
};
