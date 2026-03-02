<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('statistic_issuers')) {
            if (! Schema::hasColumn('statistic_issuers', 'data_ref')) {
                Schema::table('statistic_issuers', function (Blueprint $table) {
                    $table->date('data_ref')->nullable()->after('data');
                });
            }

            Schema::table('statistic_issuers', function (Blueprint $table) {
                $table->unique(
                    ['tenant_id', 'issuer', 'periodo', 'doc_tipo', 'tipo', 'data'],
                    'uq_statistic_issuers_cache_key'
                );

                $table->index(
                    ['tenant_id', 'issuer', 'data'],
                    'idx_statistic_issuers_tenant_issuer_data'
                );

                if (Schema::hasColumn('statistic_issuers', 'data_ref')) {
                    $table->index(
                        ['tenant_id', 'issuer', 'data_ref'],
                        'idx_statistic_issuers_tenant_issuer_data_ref'
                    );
                }
            });
        }

        if (Schema::hasTable('nfes')) {
            Schema::table('nfes', function (Blueprint $table) {
                if (Schema::hasColumn('nfes', 'tenant_id') && Schema::hasColumn('nfes', 'emitente_cnpj') && Schema::hasColumn('nfes', 'data_emissao')) {
                    $table->index(['tenant_id', 'emitente_cnpj', 'data_emissao'], 'idx_nfes_tenant_emitente_data_emissao');
                }

                if (Schema::hasColumn('nfes', 'tenant_id') && Schema::hasColumn('nfes', 'destinatario_cnpj') && Schema::hasColumn('nfes', 'data_entrada')) {
                    $table->index(['tenant_id', 'destinatario_cnpj', 'data_entrada'], 'idx_nfes_tenant_destinatario_data_entrada');
                }

                if (Schema::hasColumn('nfes', 'tenant_id') && Schema::hasColumn('nfes', 'destinatario_cnpj') && Schema::hasColumn('nfes', 'data_emissao')) {
                    $table->index(['tenant_id', 'destinatario_cnpj', 'data_emissao'], 'idx_nfes_tenant_destinatario_data_emissao');
                }
            });
        }

        if (Schema::hasTable('ctes')) {
            Schema::table('ctes', function (Blueprint $table) {
                if (Schema::hasColumn('ctes', 'tenant_id') && Schema::hasColumn('ctes', 'emitente_cnpj') && Schema::hasColumn('ctes', 'data_emissao')) {
                    $table->index(['tenant_id', 'emitente_cnpj', 'data_emissao'], 'idx_ctes_tenant_emitente_data_emissao');
                }

                if (Schema::hasColumn('ctes', 'tenant_id') && Schema::hasColumn('ctes', 'destinatario_cnpj') && Schema::hasColumn('ctes', 'data_entrada')) {
                    $table->index(['tenant_id', 'destinatario_cnpj', 'data_entrada'], 'idx_ctes_tenant_destinatario_data_entrada');
                }

                if (Schema::hasColumn('ctes', 'tenant_id') && Schema::hasColumn('ctes', 'destinatario_cnpj') && Schema::hasColumn('ctes', 'data_emissao')) {
                    $table->index(['tenant_id', 'destinatario_cnpj', 'data_emissao'], 'idx_ctes_tenant_destinatario_data_emissao');
                }
            });
        }

        if (Schema::hasTable('nfses')) {
            Schema::table('nfses', function (Blueprint $table) {
                if (Schema::hasColumn('nfses', 'tenant_id') && Schema::hasColumn('nfses', 'tomador_cnpj') && Schema::hasColumn('nfses', 'data_entrada')) {
                    $table->index(['tenant_id', 'tomador_cnpj', 'data_entrada'], 'idx_nfses_tenant_tomador_data_entrada');
                }

                if (Schema::hasColumn('nfses', 'tenant_id') && Schema::hasColumn('nfses', 'tomador_cnpj') && Schema::hasColumn('nfses', 'data_emissao')) {
                    $table->index(['tenant_id', 'tomador_cnpj', 'data_emissao'], 'idx_nfses_tenant_tomador_data_emissao');
                }

                if (Schema::hasColumn('nfses', 'tenant_id') && Schema::hasColumn('nfses', 'tomador_cnpj') && Schema::hasColumn('nfses', 'cancelada')) {
                    $table->index(['tenant_id', 'tomador_cnpj', 'cancelada'], 'idx_nfses_tenant_tomador_cancelada');
                }
            });
        }
    }

    public function down(): void
    {
        $driver = DB::getDriverName();

        if (Schema::hasTable('statistic_issuers')) {
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
                    DB::statement('DROP INDEX idx_statistic_issuers_tenant_issuer_data ON statistic_issuers');
                } elseif ($driver === 'pgsql') {
                    DB::statement('DROP INDEX IF EXISTS idx_statistic_issuers_tenant_issuer_data');
                } elseif ($driver === 'sqlite') {
                    DB::statement('DROP INDEX IF EXISTS idx_statistic_issuers_tenant_issuer_data');
                }
            } catch (\Throwable) {
            }

            if (Schema::hasColumn('statistic_issuers', 'data_ref')) {
                try {
                    if ($driver === 'mysql') {
                        DB::statement('DROP INDEX idx_statistic_issuers_tenant_issuer_data_ref ON statistic_issuers');
                    } elseif ($driver === 'pgsql') {
                        DB::statement('DROP INDEX IF EXISTS idx_statistic_issuers_tenant_issuer_data_ref');
                    } elseif ($driver === 'sqlite') {
                        DB::statement('DROP INDEX IF EXISTS idx_statistic_issuers_tenant_issuer_data_ref');
                    }
                } catch (\Throwable) {
                }

                Schema::table('statistic_issuers', function (Blueprint $table) {
                    $table->dropColumn('data_ref');
                });
            }
        }

        if (Schema::hasTable('nfes')) {
            try {
                if ($driver === 'mysql') {
                    DB::statement('DROP INDEX idx_nfes_tenant_emitente_data_emissao ON nfes');
                } elseif ($driver === 'pgsql') {
                    DB::statement('DROP INDEX IF EXISTS idx_nfes_tenant_emitente_data_emissao');
                } elseif ($driver === 'sqlite') {
                    DB::statement('DROP INDEX IF EXISTS idx_nfes_tenant_emitente_data_emissao');
                }
            } catch (\Throwable) {
            }

            try {
                if ($driver === 'mysql') {
                    DB::statement('DROP INDEX idx_nfes_tenant_destinatario_data_entrada ON nfes');
                } elseif ($driver === 'pgsql') {
                    DB::statement('DROP INDEX IF EXISTS idx_nfes_tenant_destinatario_data_entrada');
                } elseif ($driver === 'sqlite') {
                    DB::statement('DROP INDEX IF EXISTS idx_nfes_tenant_destinatario_data_entrada');
                }
            } catch (\Throwable) {
            }

            try {
                if ($driver === 'mysql') {
                    DB::statement('DROP INDEX idx_nfes_tenant_destinatario_data_emissao ON nfes');
                } elseif ($driver === 'pgsql') {
                    DB::statement('DROP INDEX IF EXISTS idx_nfes_tenant_destinatario_data_emissao');
                } elseif ($driver === 'sqlite') {
                    DB::statement('DROP INDEX IF EXISTS idx_nfes_tenant_destinatario_data_emissao');
                }
            } catch (\Throwable) {
            }
        }

        if (Schema::hasTable('ctes')) {
            try {
                if ($driver === 'mysql') {
                    DB::statement('DROP INDEX idx_ctes_tenant_emitente_data_emissao ON ctes');
                } elseif ($driver === 'pgsql') {
                    DB::statement('DROP INDEX IF EXISTS idx_ctes_tenant_emitente_data_emissao');
                } elseif ($driver === 'sqlite') {
                    DB::statement('DROP INDEX IF EXISTS idx_ctes_tenant_emitente_data_emissao');
                }
            } catch (\Throwable) {
            }

            try {
                if ($driver === 'mysql') {
                    DB::statement('DROP INDEX idx_ctes_tenant_destinatario_data_entrada ON ctes');
                } elseif ($driver === 'pgsql') {
                    DB::statement('DROP INDEX IF EXISTS idx_ctes_tenant_destinatario_data_entrada');
                } elseif ($driver === 'sqlite') {
                    DB::statement('DROP INDEX IF EXISTS idx_ctes_tenant_destinatario_data_entrada');
                }
            } catch (\Throwable) {
            }

            try {
                if ($driver === 'mysql') {
                    DB::statement('DROP INDEX idx_ctes_tenant_destinatario_data_emissao ON ctes');
                } elseif ($driver === 'pgsql') {
                    DB::statement('DROP INDEX IF EXISTS idx_ctes_tenant_destinatario_data_emissao');
                } elseif ($driver === 'sqlite') {
                    DB::statement('DROP INDEX IF EXISTS idx_ctes_tenant_destinatario_data_emissao');
                }
            } catch (\Throwable) {
            }
        }

        if (Schema::hasTable('nfses')) {
            try {
                if ($driver === 'mysql') {
                    DB::statement('DROP INDEX idx_nfses_tenant_tomador_data_entrada ON nfses');
                } elseif ($driver === 'pgsql') {
                    DB::statement('DROP INDEX IF EXISTS idx_nfses_tenant_tomador_data_entrada');
                } elseif ($driver === 'sqlite') {
                    DB::statement('DROP INDEX IF EXISTS idx_nfses_tenant_tomador_data_entrada');
                }
            } catch (\Throwable) {
            }

            try {
                if ($driver === 'mysql') {
                    DB::statement('DROP INDEX idx_nfses_tenant_tomador_data_emissao ON nfses');
                } elseif ($driver === 'pgsql') {
                    DB::statement('DROP INDEX IF EXISTS idx_nfses_tenant_tomador_data_emissao');
                } elseif ($driver === 'sqlite') {
                    DB::statement('DROP INDEX IF EXISTS idx_nfses_tenant_tomador_data_emissao');
                }
            } catch (\Throwable) {
            }

            try {
                if ($driver === 'mysql') {
                    DB::statement('DROP INDEX idx_nfses_tenant_tomador_cancelada ON nfses');
                } elseif ($driver === 'pgsql') {
                    DB::statement('DROP INDEX IF EXISTS idx_nfses_tenant_tomador_cancelada');
                } elseif ($driver === 'sqlite') {
                    DB::statement('DROP INDEX IF EXISTS idx_nfses_tenant_tomador_cancelada');
                }
            } catch (\Throwable) {
            }
        }
    }
};
