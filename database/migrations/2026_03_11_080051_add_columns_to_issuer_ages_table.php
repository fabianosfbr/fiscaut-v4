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
        Schema::table('issuer_ages', function (Blueprint $table) {
            $table->enum('type', ['AGO', 'AGE'])->default('AGE')->after('tenant_id');
            $table->date('data_limite_ago')->nullable()->after('type');
            $table->string('prazo_tecnico_edital')->nullable()->after('data_limite_ago');
            $table->date('mandato_fim')->nullable()->after('prazo_tecnico_edital');
            $table->string('prazo_tecnico_mandato')->nullable()->after('mandato_fim');
            $table->date('mandato_conselho_fim')->nullable()->after('prazo_tecnico_mandato');
            $table->string('prazo_tecnico_mandato_conselho')->nullable()->after('mandato_conselho_fim');
            $table->date('mandato_banco_fim')->nullable()->after('prazo_tecnico_mandato_conselho');
            $table->string('prazo_tecnico_mandato_banco')->nullable()->after('mandato_banco_fim');
            $table->integer('boleto_dia_vencimento')->nullable()->after('prazo_tecnico_mandato_banco');
            $table->string('boleto_tipo_prazo')->nullable()->after('boleto_dia_vencimento');
            $table->string('boleto_gerado_por')->nullable()->after('boleto_tipo_prazo');
            $table->string('boleto_forma_rateio')->nullable()->after('boleto_gerado_por');
            $table->boolean('tem_isencao_remuneracao')->default(false)->after('boleto_forma_rateio');
            $table->json('quem_recebe_isencao')->nullable()->after('tem_isencao_remuneracao');
            $table->decimal('valor_isencao_remuneracao', 10, 2)->nullable()->after('quem_recebe_isencao');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('issuer_ages', function (Blueprint $table) {
            $table->dropColumn([
                'type',
                'data_limite_ago',
                'prazo_tecnico_edital',
                'mandato_fim',
                'prazo_tecnico_mandato',
                'mandato_conselho_fim',
                'prazo_tecnico_mandato_conselho',
                'mandato_banco_fim',
                'prazo_tecnico_mandato_banco',
                'boleto_dia_vencimento',
                'boleto_tipo_prazo',
                'boleto_gerado_por',
                'boleto_forma_rateio',
                'tem_isencao_remuneracao',
                'quem_recebe_isencao',
                'valor_isencao_remuneracao',
            ]);
        });
    }
};
