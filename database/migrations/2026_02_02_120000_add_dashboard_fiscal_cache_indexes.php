<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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
        if (Schema::hasTable('statistic_issuers')) {
            Schema::table('statistic_issuers', function (Blueprint $table) {
                $table->dropUnique('uq_statistic_issuers_cache_key');
                $table->dropIndex('idx_statistic_issuers_tenant_issuer_data');

                if (Schema::hasColumn('statistic_issuers', 'data_ref')) {
                    $table->dropIndex('idx_statistic_issuers_tenant_issuer_data_ref');
                }
            });

            if (Schema::hasColumn('statistic_issuers', 'data_ref')) {
                Schema::table('statistic_issuers', function (Blueprint $table) {
                    $table->dropColumn('data_ref');
                });
            }
        }

        if (Schema::hasTable('nfes')) {
            Schema::table('nfes', function (Blueprint $table) {
                $table->dropIndex('idx_nfes_tenant_emitente_data_emissao');
                $table->dropIndex('idx_nfes_tenant_destinatario_data_entrada');
                $table->dropIndex('idx_nfes_tenant_destinatario_data_emissao');
            });
        }

        if (Schema::hasTable('ctes')) {
            Schema::table('ctes', function (Blueprint $table) {
                $table->dropIndex('idx_ctes_tenant_emitente_data_emissao');
                $table->dropIndex('idx_ctes_tenant_destinatario_data_entrada');
                $table->dropIndex('idx_ctes_tenant_destinatario_data_emissao');
            });
        }

        if (Schema::hasTable('nfses')) {
            Schema::table('nfses', function (Blueprint $table) {
                $table->dropIndex('idx_nfses_tenant_tomador_data_entrada');
                $table->dropIndex('idx_nfses_tenant_tomador_data_emissao');
                $table->dropIndex('idx_nfses_tenant_tomador_cancelada');
            });
        }
    }
};

