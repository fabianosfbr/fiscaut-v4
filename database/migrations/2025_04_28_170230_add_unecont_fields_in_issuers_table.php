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
            $table->timestamp('unecont_registered_at')->nullable();
            $table->timestamp('unecont_unregistered_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('issuers', function (Blueprint $table) {
            $table->dropColumn('unecont_registered_at');
            $table->dropColumn('unecont_unregistered_at');
        });
    }
};
