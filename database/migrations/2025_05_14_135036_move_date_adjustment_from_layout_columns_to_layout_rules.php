<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Primeiro, adiciona a coluna na tabela de regras
        Schema::table('contabil_layout_rules', function (Blueprint $table) {
            $table->string('date_adjustment')->default('same')->nullable()->after('data_source_type');
        });

        // Migra dados das colunas para regras (apenas para regras de tipo data_da_operacao)
        $this->migrateDataFromColumnsToRules();

        // Remove a coluna da tabela de colunas
        Schema::table('contabil_layout_columns', function (Blueprint $table) {
            $table->dropColumn('date_adjustment');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Adiciona a coluna de volta à tabela de colunas
        Schema::table('contabil_layout_columns', function (Blueprint $table) {
            $table->string('date_adjustment')->default('same')->after('format');
        });

        // Migra dados das regras para colunas (de volta)
        $this->migrateDataFromRulesToColumns();

        // Remove a coluna da tabela de regras
        Schema::table('contabil_layout_rules', function (Blueprint $table) {
            $table->dropColumn('date_adjustment');
        });
    }

    /**
     * Migra dados de ajuste de data das colunas para as regras
     */
    private function migrateDataFromColumnsToRules(): void
    {
        // Obter todas as regras do tipo data_da_operacao com fonte de dados como coluna
        $rules = DB::table('contabil_layout_rules')
            ->where('rule_type', 'data_da_operacao')
            ->where('data_source_type', 'column')
            ->get();

        foreach ($rules as $rule) {
            // Obter o ajuste de data da coluna correspondente
            $column = DB::table('contabil_layout_columns')
                ->where('layout_id', $rule->layout_id)
                ->where('target_column_name', $rule->data_source)
                ->first();

            if ($column && $column->date_adjustment) {
                // Atualizar a regra com o ajuste de data da coluna
                DB::table('contabil_layout_rules')
                    ->where('id', $rule->id)
                    ->update(['date_adjustment' => $column->date_adjustment]);
            }
        }
    }

    /**
     * Migra dados de ajuste de data das regras para as colunas
     */
    private function migrateDataFromRulesToColumns(): void
    {
        // Obter todas as regras do tipo data_da_operacao com fonte de dados como coluna
        $rules = DB::table('contabil_layout_rules')
            ->where('rule_type', 'data_da_operacao')
            ->where('data_source_type', 'column')
            ->whereNotNull('date_adjustment')
            ->get();

        foreach ($rules as $rule) {
            // Atualizar a coluna correspondente com o ajuste de data da regra
            DB::table('contabil_layout_columns')
                ->where('layout_id', $rule->layout_id)
                ->where('target_column_name', $rule->data_source)
                ->update(['date_adjustment' => $rule->date_adjustment]);
        }
    }
};
