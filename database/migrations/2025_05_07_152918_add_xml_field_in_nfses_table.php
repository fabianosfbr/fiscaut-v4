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
        Schema::table('nfses', function (Blueprint $table) {
            //if column xml not exists, add it
            if (!Schema::hasColumn('nfses', 'xml')) {
                $table->longText('xml')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nfses', function (Blueprint $table) {
            $table->dropColumn('xml');
        });
    }
};
