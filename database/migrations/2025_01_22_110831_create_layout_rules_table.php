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
        Schema::create('contabil_layout_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('layout_id')->references('id')->on('contabil_layouts')->cascadeOnDelete();
            $table->integer('position');
            $table->string('data_source_type');
            $table->text('data_source')->nullable();
            $table->text('data_source_constant')->nullable(); //alterado
            $table->text('data_source_query')->nullable(); //add
            $table->string('data_format')->nullable();
            $table->string('format_string')->nullable();
            $table->string('condition_type')->default('none');
            $table->text('condition')->nullable();
            $table->string('condition_data_source_type')->nullable();
            $table->text('condition_data_source')->nullable();
            $table->text('condition_data_source_constant')->nullable(); //alterado
            $table->text('condition_data_source_query')->nullable(); //add
            $table->string('condition_operator')->nullable();
            $table->string('condition_value')->nullable();
            $table->string('default_value')->nullable();
            $table->boolean('has_condition')->default(false);

            $table->string('data_source_table')->nullable();
            $table->string('data_source_attribute')->nullable();
            $table->string('data_source_condition')->nullable();
            $table->string('data_source_value_type')->nullable();
            $table->string('data_source_search_value')->nullable();
            $table->string('data_source_search_constant')->nullable();

            $table->string('condition_data_source_table')->nullable();
            $table->string('condition_data_source_attribute')->nullable();
            $table->string('condition_data_source_condition')->nullable();
            $table->string('condition_data_source_value_type')->nullable();
            $table->string('condition_data_source_search_value')->nullable();
            $table->string('condition_data_source_search_constant')->nullable();


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contabil_layout_rules');
    }
};
