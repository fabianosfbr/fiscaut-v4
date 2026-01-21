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
        Schema::table('simples_nacional_aliquotas', function (Blueprint $table) {
            $table->decimal('ipi_percentual', 5, 2)->after('cpp_percentual')->default(0.00);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('simples_nacional_aliquotas', function (Blueprint $table) {
            $table->dropColumn('ipi_percentual');
        });
    }
};
