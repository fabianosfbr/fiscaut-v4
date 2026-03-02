<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('statistic_issuers')) {
            return;
        }

        if (! Schema::hasColumn('statistic_issuers', 'metrica')) {
            Schema::table('statistic_issuers', function (Blueprint $table) {
                $table->string('metrica', 50)->default('qtd')->after('tipo');
            });
        }

        DB::table('statistic_issuers')->whereNull('metrica')->update(['metrica' => 'qtd']);

        $driver = DB::getDriverName();
        try {
            if ($driver === 'mysql') {
                DB::statement('DROP INDEX uq_statistic_issuers_cache_key ON statistic_issuers');
            } elseif ($driver === 'pgsql') {
                DB::statement('DROP INDEX IF EXISTS uq_statistic_issuers_cache_key');
            } elseif ($driver === 'sqlite') {
                DB::statement('DROP INDEX IF EXISTS uq_statistic_issuers_cache_key');
            }
        } catch (\Throwable) {
        }

        Schema::table('statistic_issuers', function (Blueprint $table) {
            $table->unique(
                ['tenant_id', 'issuer', 'periodo', 'doc_tipo', 'tipo', 'metrica', 'data'],
                'uq_statistic_issuers_cache_key'
            );

            $table->index(
                ['tenant_id', 'issuer', 'metrica', 'data'],
                'idx_statistic_issuers_tenant_issuer_metrica_data'
            );
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('statistic_issuers')) {
            return;
        }

        $driver = DB::getDriverName();
        try {
            if ($driver === 'mysql') {
                DB::statement('DROP INDEX uq_statistic_issuers_cache_key ON statistic_issuers');
            } elseif ($driver === 'pgsql') {
                DB::statement('DROP INDEX IF EXISTS uq_statistic_issuers_cache_key');
            } elseif ($driver === 'sqlite') {
                DB::statement('DROP INDEX IF EXISTS uq_statistic_issuers_cache_key');
            }
        } catch (\Throwable) {
        }

        try {
            if ($driver === 'mysql') {
                DB::statement('DROP INDEX idx_statistic_issuers_tenant_issuer_metrica_data ON statistic_issuers');
            } elseif ($driver === 'pgsql') {
                DB::statement('DROP INDEX IF EXISTS idx_statistic_issuers_tenant_issuer_metrica_data');
            } elseif ($driver === 'sqlite') {
                DB::statement('DROP INDEX IF EXISTS idx_statistic_issuers_tenant_issuer_metrica_data');
            }
        } catch (\Throwable) {
        }

        // Drop orphaned indexes from previous migration
        try {
            if ($driver === 'mysql') {
                DB::statement('DROP INDEX IF EXISTS idx_statistic_issuers_tenant_issuer_data ON statistic_issuers');
            } elseif ($driver === 'pgsql') {
                DB::statement('DROP INDEX IF EXISTS idx_statistic_issuers_tenant_issuer_data');
            } elseif ($driver === 'sqlite') {
                DB::statement('DROP INDEX IF EXISTS idx_statistic_issuers_tenant_issuer_data');
            }
        } catch (\Throwable) {
        }

        try {
            if ($driver === 'mysql') {
                DB::statement('DROP INDEX IF EXISTS idx_statistic_issuers_tenant_issuer_data_ref ON statistic_issuers');
            } elseif ($driver === 'pgsql') {
                DB::statement('DROP INDEX IF EXISTS idx_statistic_issuers_tenant_issuer_data_ref');
            } elseif ($driver === 'sqlite') {
                DB::statement('DROP INDEX IF EXISTS idx_statistic_issuers_tenant_issuer_data_ref');
            }
        } catch (\Throwable) {
        }

        Schema::table('statistic_issuers', function (Blueprint $table) {
            $table->unique(
                ['tenant_id', 'issuer', 'periodo', 'doc_tipo', 'tipo', 'data'],
                'uq_statistic_issuers_cache_key'
            );
        });

        if (Schema::hasColumn('statistic_issuers', 'metrica')) {
            Schema::table('statistic_issuers', function (Blueprint $table) {
                $table->dropColumn('metrica');
            });
        }
    }
};
