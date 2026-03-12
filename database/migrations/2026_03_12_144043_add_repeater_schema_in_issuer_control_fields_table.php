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
        Schema::table('issuer_control_fields', function (Blueprint $table) {
            $table->json('repeater_schema')->nullable()->after('input_mask');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('issuer_control_fields', function (Blueprint $table) {
            $table->dropColumn('repeater_schema');
        });
    }
};
