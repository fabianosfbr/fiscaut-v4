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
        Schema::table('issuer_assembleias', function (Blueprint $table) {
            $table->string('status_ata')
                ->nullable()
                ->after('data_limite_ago');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('issuer_assembleias', function (Blueprint $table) {
            $table->dropColumn('status_ata');
        });
    }
};
