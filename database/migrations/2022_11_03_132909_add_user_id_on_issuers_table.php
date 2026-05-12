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
        if (Schema::hasColumn('issuers', 'user_id')) {
            return;
        }

        Schema::table('issuers', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (! Schema::hasColumn('issuers', 'user_id')) {
            return;
        }

        Schema::table('issuers', function (Blueprint $table) {
            try {
                $table->dropConstrainedForeignId('user_id');
            } catch (Throwable $e) {
                try {
                    $table->dropColumn('user_id');
                } catch (Throwable $e) {
                }
            }
        });
    }
};
