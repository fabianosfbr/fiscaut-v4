<?php

namespace Database\Seeders;

use App\Enums\ManutencaoCategoriaEnum;
use App\Enums\ManutencaoFrequenciaEnum;
use App\Enums\ManutencaoPrioridadeEnum;
use App\Enums\ManutencaoStatusEnum;
use App\Models\Manutencao;
use App\Models\ManutencaoHistorico;
use App\Models\ManutencaoRecorrencia;
use App\Models\TipoManutencao;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ManutencaoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $issuerId = 60;

        // 1. Criar Tipos de Manutenção
        $this->criarTiposManutencao($issuerId);

        // 2. Criar Templates de Recorrência
        $this->criarTemplatesRecorrencia($issuerId);

        // 3. Criar Manutenções Individuais
        $this->criarManutencoesIndividuais($issuerId);

        // 4. Criar Histórico de Alterações de Status
        $this->criarHistoricoStatus($issuerId);

        $this->command->info('Seed de Manutenções concluída com sucesso!');
    }

    private function criarTiposManutencao(int $issuerId): void
    {
        $this->command->info('Criando Tipos de Manutenção...');

        $tipos = [
            [
                'issuer_id' => $issuerId,
                'nome' => 'AVCB',
                'categoria' => ManutencaoCategoriaEnum::ADMINISTRATIVA->value,
                'descricao' => 'Certificado de Vistoria do Corpo de Bombeiros',
                'alerta_dias_antecedencia' => 15,
                'prioridade' => ManutencaoPrioridadeEnum::ALTA->value,
                'responsavel_padrao' => 'Administração',
                'ativo' => true,
            ],
            [
                'issuer_id' => $issuerId,
                'nome' => 'Renovação de Alvará de Funcionamento',
                'categoria' => ManutencaoCategoriaEnum::ADMINISTRATIVA->value,
                'descricao' => 'Renovação de Alvará de Funcionamento',
                'alerta_dias_antecedencia' => 15,
                'prioridade' => ManutencaoPrioridadeEnum::ALTA->value,
                'responsavel_padrao' => 'Administração',
                'ativo' => true,
            ],
            [
                'issuer_id' => $issuerId,
                'nome' => 'Limpeza de Caixa D\'Água',
                'categoria' => ManutencaoCategoriaEnum::PREVENTIVA->value,
                'descricao' => 'Limpeza e higienização da caixa d\'água para garantir a qualidade da água',
                'alerta_dias_antecedencia' => 15,
                'prioridade' => ManutencaoPrioridadeEnum::ALTA->value,
                'responsavel_padrao' => 'Manutenção Hidráulica',
                'ativo' => true,
            ],
            [
                'issuer_id' => $issuerId,
                'nome' => 'Troca de Lâmpadas da Garagem',
                'categoria' => ManutencaoCategoriaEnum::CORRETIVA->value,
                'descricao' => 'Substituição de lâmpadas queimadas na área de garagem',
                'alerta_dias_antecedencia' => 0,
                'prioridade' => ManutencaoPrioridadeEnum::BAIXA->value,
                'responsavel_padrao' => 'Manutenção Elétrica',
                'ativo' => true,
            ],
            [
                'issuer_id' => $issuerId,
                'nome' => 'Inspeção de Segurança',
                'categoria' => ManutencaoCategoriaEnum::INSPECAO->value,
                'descricao' => 'Inspeção de equipamentos de segurança e sinalização',
                'alerta_dias_antecedencia' => 3,
                'prioridade' => ManutencaoPrioridadeEnum::ALTA->value,
                'responsavel_padrao' => 'Segurança do Trabalho',
                'ativo' => true,
            ],
            [
                'issuer_id' => $issuerId,
                'nome' => 'Pintura de Fachada',
                'categoria' => ManutencaoCategoriaEnum::PREVENTIVA->value,
                'descricao' => 'Pintura preventiva da fachada para proteção contra intempéries',
                'alerta_dias_antecedencia' => 30,
                'prioridade' => ManutencaoPrioridadeEnum::MEDIA->value,
                'responsavel_padrao' => 'Empresa Terceirizada',
                'ativo' => true,
            ],
        ];

        foreach ($tipos as $tipo) {
            TipoManutencao::create($tipo);
        }

        $this->command->info('Tipos de Manutenção criados com sucesso!');
    }

    private function criarTemplatesRecorrencia(int $issuerId): void
    {
        $this->command->info('Criando Templates de Recorrência...');

        $tipos = TipoManutencao::where('issuer_id', $issuerId)->get();

        $templates = [
            [
                'issuer_id' => $issuerId,
                'tipo_manutencao_id' => $tipos->where('nome', 'Limpeza de Caixa D\'Água')->first()->id,
                'titulo_template' => 'Limpeza de Caixa D\'Água - {data}',
                'descricao_template' => 'Limpeza preventiva da caixa d\'água conforme normas de qualidade',
                'frequencia' => ManutencaoFrequenciaEnum::MENSAL->value,
                'intervalo' => 1,
                'dia_mes' => 15,
                'data_inicio' => '2024-01-01',
                'data_fim' => '2025-12-31',
                'gerar_dias_antecedencia' => 7,
                'ativo' => true,
            ],
            [
                'issuer_id' => $issuerId,
                'tipo_manutencao_id' => $tipos->where('nome', 'Inspeção de Segurança')->first()->id,
                'titulo_template' => 'Inspeção de Segurança - {data}',
                'descricao_template' => 'Inspeção mensal de equipamentos de segurança',
                'frequencia' => ManutencaoFrequenciaEnum::SEMANAL->value,
                'intervalo' => 1,
                'dia_semana' => 1, // Segunda-feira
                'data_inicio' => '2024-01-01',
                'data_fim' => '2024-12-31',
                'gerar_dias_antecedencia' => 3,
                'ativo' => true,
            ],
        ];

        foreach ($templates as $template) {
            ManutencaoRecorrencia::create($template);
        }

        $this->command->info('Templates de Recorrência criados com sucesso!');
    }

    private function criarManutencoesIndividuais(int $issuerId): void
    {
        $this->command->info('Criando Manutenções Individuais...');

        $tipos = TipoManutencao::where('issuer_id', $issuerId)->get();

        // Data base para simulação
        $dataBase = now()->subDays(10);

        $manutencoes = [
            // Manutenção 1: Limpeza de Caixa D'Água (com histórico de status)
            [
                'issuer_id' => $issuerId,
                'tipo_manutencao_id' => $tipos->where('nome', 'Limpeza de Caixa D\'Água')->first()->id,
                'titulo' => 'Limpeza de Caixa D\'Água - 15/03/2024',
                'descricao' => 'Limpeza preventiva da caixa d\'água conforme normas de qualidade',
                'status' => ManutencaoStatusEnum::CONCLUIDA->value,
                'prioridade' => 'alta',
                'data_programada' => $dataBase->copy()->addDays(10), // Hoje - 10 dias
                'usuario_responsavel' => 'João Silva (Empresa de Limpeza ABC)',
                'custo_real' => 250.00,
                'observacoes' => 'Limpeza concluída com sucesso, caixa d\'água higienizada e pronta para uso',

            ],
            // Manutenção 2: Troca de Lâmpadas (em andamento)
            [
                'issuer_id' => $issuerId,
                'tipo_manutencao_id' => $tipos->where('nome', 'Troca de Lâmpadas da Garagem')->first()->id,
                'titulo' => 'Troca de Lâmpadas Queimadas - Garagem',
                'descricao' => 'Substituição de lâmpadas queimadas no corredor da garagem',
                'status' => ManutencaoStatusEnum::EM_ANDAMENTO->value,
                'prioridade' => 'baixa',
                'data_programada' => $dataBase->copy()->addDays(12), // Hoje - 8 dias
                'usuario_responsavel' => 'Carlos Oliveira (Manutenção Elétrica)',
                'custo_real' => null,
                'observacoes' => 'Início da troca de lâmpadas, aguardando entrega de peças',

            ],
            // Manutenção 3: Pintura de Fachada (programada)
            [
                'issuer_id' => $issuerId,
                'tipo_manutencao_id' => $tipos->where('nome', 'Pintura de Fachada')->first()->id,
                'titulo' => 'Pintura de Fachada - Edifício Principal',
                'descricao' => 'Pintura preventiva da fachada para proteção contra intempéries',
                'status' => ManutencaoStatusEnum::PROGRAMADA->value,
                'prioridade' => 'media',
                'data_programada' => $dataBase->copy()->addDays(16), // Hoje - 4 dias
                'usuario_responsavel' => null,
                'custo_real' => null,
                'observacoes' => 'Agendado com empresa terceirizada para próxima semana',

            ],
            // Manutenção 4: Inspeção de Segurança (atrasada)
            [
                'issuer_id' => $issuerId,
                'tipo_manutencao_id' => $tipos->where('nome', 'Inspeção de Segurança')->first()->id,
                'titulo' => 'Inspeção de Segurança - 18/03/2024',
                'descricao' => 'Inspeção mensal de equipamentos de segurança',
                'status' => ManutencaoStatusEnum::ATRASADA->value,
                'prioridade' => 'alta',
                'data_programada' => $dataBase->copy()->addDays(8), // Hoje - 12 dias
                'usuario_responsavel' => null,
                'custo_real' => null,
                'observacoes' => 'Inspeção atrasada por falta de equipe disponível',

            ],
        ];

        foreach ($manutencoes as $manutencao) {
            Manutencao::create($manutencao);
        }

        $this->command->info('Manutenções Individuais criadas com sucesso!');
    }

    private function criarHistoricoStatus(int $issuerId): void
    {
        $this->command->info('Criando Histórico de Alterações de Status...');

        $manutencoes = Manutencao::where('issuer_id', $issuerId)->get();

        // Histórico para a manutenção concluída (Limpeza de Caixa D'Água)
        $manutencao1 = $manutencoes->where('titulo', 'Limpeza de Caixa D\'Água - 15/03/2024')->first();
        if ($manutencao1) {
            $dataBase = now()->subDays(14);

            // Programada → Em Andamento (2 dias depois)
            ManutencaoHistorico::create([
                'manutencao_id' => $manutencao1->id,
                'acao' => 'status',
                'status_anterior' => 'programada',
                'status_novo' => 'em_andamento',
                'usuario_id' => 1, // Supondo que exista um usuário administrador
                'observacao' => 'Início da limpeza da caixa d\'água',
                'created_at' => $dataBase->copy()->addDays(2),

            ]);

            // Em Andamento → Concluída (2 dias depois)
            ManutencaoHistorico::create([
                'manutencao_id' => $manutencao1->id,
                'acao' => 'status',
                'status_anterior' => 'em_andamento',
                'status_novo' => 'concluida',
                'usuario_id' => 1,
                'observacao' => 'Limpeza concluída com sucesso, caixa d\'água higienizada',
                'created_at' => $dataBase->copy()->addDays(4),

            ]);

            // Alteração de custo real
            ManutencaoHistorico::create([
                'manutencao_id' => $manutencao1->id,
                'acao' => 'custo_real',
                'status_anterior' => null,
                'status_novo' => '250.00',
                'usuario_id' => 1,
                'observacao' => 'Registro do custo real da limpeza',
                'created_at' => $dataBase->copy()->addDays(4),

            ]);
        }

        // Histórico para a manutenção em andamento (Troca de Lâmpadas)
        $manutencao2 = $manutencoes->where('titulo', 'Troca de Lâmpadas Queimadas - Garagem')->first();
        if ($manutencao2) {
            $dataBase = now()->subDays(12);

            // Programada → Em Andamento
            ManutencaoHistorico::create([
                'manutencao_id' => $manutencao2->id,
                'acao' => 'status',
                'status_anterior' => 'programada',
                'status_novo' => 'em_andamento',
                'usuario_id' => 1,
                'observacao' => 'Início da troca de lâmpadas',
                'created_at' => $dataBase->copy()->addDays(2),

            ]);
        }

        // Histórico para a manutenção atrasada (Inspeção de Segurança)
        $manutencao4 = $manutencoes->where('titulo', 'Inspeção de Segurança - 18/03/2024')->first();
        if ($manutencao4) {
            $dataBase = now()->subDays(16);

            // Programada → Atrasada (quando passou da data programada)
            ManutencaoHistorico::create([
                'manutencao_id' => $manutencao4->id,
                'acao' => 'status',
                'status_anterior' => 'programada',
                'status_novo' => 'atrasada',
                'usuario_id' => 1,
                'observacao' => 'Manutenção atrasada por falta de equipe disponível',
                'created_at' => $dataBase->copy()->addDays(10),

            ]);
        }

        $this->command->info('Histórico de Alterações de Status criado com sucesso!');
    }
}
