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
        Schema::create('contabil_layout_columns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('layout_id')->references('id')->on('contabil_layouts');
            $table->string('excel_column_name');
            $table->string('target_column_name');
            $table->string('data_type')->default('text'); // text, number, date, etc.
            $table->string('format')->nullable(); // Para formatos de data e número
            $table->boolean('is_required')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contabil_layout_columns');
    }
};
