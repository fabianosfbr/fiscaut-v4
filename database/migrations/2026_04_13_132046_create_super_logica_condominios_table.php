<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('super_logica_condominios', function (Blueprint $table) {
            $table->id();
            $table->integer('id_condominio_cond')->nullable();
            $table->integer('id_uf_uf')->nullable();
            $table->string('st_uf_uf', 2)->nullable();
            $table->string('st_cep_cond', 10)->nullable();
            $table->string('st_url_cond')->nullable();
            $table->string('st_inscrestadual_cond')->nullable();
            $table->string('fl_tipocond_cond')->nullable();
            $table->integer('id_planoconta_plc')->nullable();
            $table->integer('dt_diadebito_cond')->nullable();
            $table->integer('dt_diavencimento_cond')->nullable();
            $table->integer('id_tipocobranca_tco')->nullable();
            $table->string('st_cnpj_cond', 20)->nullable();
            $table->string('st_email_cond')->nullable();
            $table->string('st_cpf_cond', 20)->nullable();
            $table->string('st_estado_cond', 2)->nullable();
            $table->string('st_telefone_cond', 15)->nullable();
            $table->text('st_observacao_cond')->nullable();
            $table->string('st_fax_cond', 15)->nullable();
            $table->string('st_nome_cond')->nullable();
            $table->string('st_endereco_cond')->nullable();
            $table->string('st_complemento_cond')->nullable();
            $table->string('st_bairro_cond')->nullable();
            $table->string('st_cidade_cond')->nullable();
            $table->string('st_fantasia_cond')->nullable();
            $table->tinyInteger('fl_ativo_cond')->default(1);
            $table->decimal('nm_txfundocx_cond', 10, 2)->default(0.00);
            $table->decimal('nm_txjuros_cond', 10, 2)->default(1.00);
            $table->decimal('nm_txmulta_cond', 10, 2)->default(2.00);
            $table->decimal('st_inadimplente_cond', 10, 2)->default(0.00);
            $table->integer('id_tipocondominio_tcon')->nullable();
            $table->integer('id_tipojuros_cond')->nullable();
            $table->tinyInteger('fl_jurosatualizar_vl_base_cond')->default(1);
            $table->tinyInteger('fl_multaatualizar_vl_base_cond')->default(1);
            $table->string('nm_diasparaatualizar_cond')->nullable();
            $table->tinyInteger('fl_juroscompostos_cond')->default(1);
            $table->tinyInteger('fl_multaprorata_cond')->default(0);
            $table->string('nm_multamaxima_cond')->nullable();
            $table->string('st_label_cond')->nullable();
            $table->decimal('nm_txhonorario_cond', 10, 2)->default(0.00);
            $table->string('id_contato_vazia_con')->nullable();
            $table->string('nm_txdesconto_cond')->nullable();
            $table->integer('nm_descontoatedia_cond')->default(0);
            $table->tinyInteger('fl_descontovalorfixo_cond')->default(0);
            $table->tinyInteger('fl_multasobrejuros_cond')->default(0);
            $table->string('nm_txdesconto2_cond')->nullable();
            $table->string('nm_txdesconto3_cond')->nullable();
            $table->string('nm_descontoatedia2_cond')->nullable();
            $table->string('nm_descontoatedia3_cond')->nullable();
            $table->string('fl_descontovalorfixo2_cond')->nullable();
            $table->string('fl_descontovalorfixo3_cond')->nullable();
            $table->string('fl_descontoapenascontas_cond')->nullable();
            $table->date('dt_fechamento_cond')->nullable();
            $table->string('id_licitamais_cond')->nullable();
            $table->string('st_token_emp')->nullable();
            $table->string('st_numeroendereco_cond')->nullable();
            $table->string('st_credencialpj_cond')->nullable();
            $table->string('st_chavepj_cond')->nullable();
            $table->string('st_inscrmunicipal_cond')->nullable();
            $table->integer('id_cnae_cnae')->nullable();
            $table->integer('id_classificacaotributaria_ctri')->nullable();
            $table->integer('id_naturezajuridica_njur')->nullable();
            $table->timestamp('dt_ativacao_cond')->nullable();
            $table->timestamp('dt_desativacao_cond')->nullable();
            $table->timestamp('dt_criacao_cond')->nullable();
            $table->string('nm_metrosquadrados_cond')->nullable();
            $table->tinyInteger('fl_tipoestrutura_cond')->default(0);
            $table->date('dt_inauguracao_cond')->nullable();
            $table->string('nm_funcionarios_cond')->nullable();
            $table->string('nm_vagas_cond')->nullable();
            $table->string('fl_usabpo_cond')->nullable();
            $table->string('fl_garantido_cond')->nullable();
            $table->string('st_accesskeybpocobranca_cond')->nullable();
            $table->tinyInteger('fl_tipocondominio_cond')->default(0);
            $table->tinyInteger('fl_elevadorbloco_cond')->default(0);
            $table->tinyInteger('fl_hidrantes_cond')->default(0);
            $table->tinyInteger('fl_extintores_cond')->default(0);
            $table->tinyInteger('fl_sprinklers_cond')->default(0);
            $table->tinyInteger('fl_detector_cond')->default(0);
            $table->tinyInteger('fl_manobrista_cond')->default(0);
            $table->timestamp('dt_alteracao_cond')->nullable();
            $table->tinyInteger('fl_status_implantacao')->default(1);
            $table->decimal('nm_txjurosacordo_cond', 10, 2)->default(1.00);
            $table->decimal('nm_txmultaacordo_cond', 10, 2)->default(2.00);
            $table->string('id_escritorio_esc')->nullable();
            $table->decimal('vl_limitecredito_cond', 10, 2)->default(0.00);
            $table->tinyInteger('fl_centralizadorefdreinf_cond')->default(0);
            $table->string('st_fracao_cond')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('super_logica_condominios');
    }
};
