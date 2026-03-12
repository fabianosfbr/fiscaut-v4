<?php

use App\Console\Scheduling\DynamicTaskCommandExecutor;
use App\Models\IssuerControlField;
use App\Models\NotaFiscalEletronica;
use Illuminate\Support\Facades\Artisan;

Artisan::command('play', function () {

    IssuerControlField::insert([
        [
            'issuer_id' => 60,
            'issuer_group_control_id' => 4,
            'key' => 'manutencoes_programadas',
            'label' => 'Manutenções programadas',
            'type' => 'repeater',
            'required' => false,
            'order' => 1,
            'repeater_schema' => [
                [
                    'name' => 'tipo',
                    'label' => 'Tipo de manutenção',
                    'type' => 'select',
                    'required' => true,
                    'options' => [
                        "Caixa d'água" => "Caixa d'água",
                        'Extintor' => 'Extintor',
                        'Pressurização' => 'Pressurização',
                        'Caixa de gordura' => 'Caixa de gordura',
                        'Dedetização' => 'Dedetização',
                        'Teste de Mangueiras de incêndio' => 'Teste de Mangueiras de incêndio',
                        'Pluvial' => 'Pluvial',
                        'Calhas' => 'Calhas',
                        'Gerador' => 'Gerador',
                        'Teste de água (ph, etc)' => 'Teste de água (ph, etc)',
                        'Iluminação de emergência' => 'Iluminação de emergência',
                        'Teste alarme de incêndio / sprinter' => 'Teste alarme de incêndio / sprinter',
                        'Vistoria playground' => 'Vistoria playground',
                        'Manutenção periódica Elevador' => 'Manutenção periódica Elevador',
                        'Aquecedores' => 'Aquecedores',
                        'Manutenção de ar condicionado' => 'Manutenção de ar condicionado',
                        'Pintura fachada' => 'Pintura fachada',
                        'Hidrantes' => 'Hidrantes',
                        'Inventário – Ativo imobilizado' => 'Inventário – Ativo imobilizado',
                        'Outro' => 'Outro',
                    ],
                ],
                [
                    'name' => 'outro',
                    'label' => 'Outro (se aplicável)',
                    'type' => 'text',
                    'required' => false,
                    'placeholder' => 'Descreva a manutenção',
                ],
                [
                    'name' => 'data_programada',
                    'label' => 'Data de programação',
                    'type' => 'text',
                    'required' => false,
                    'mask' => '99/99/9999',
                    'placeholder' => 'DD/MM/AAAA',
                ],
            ],
        ],
    ]);

    $repeater  = [
                [
                    'name' => 'tipo',
                    'label' => 'Tipo de manutenção',
                    'type' => 'select',
                    'required' => true,
                    'options' => [
                        "Caixa d'água" => "Caixa d'água",
                        'Extintor' => 'Extintor',
                        'Pressurização' => 'Pressurização',
                        'Caixa de gordura' => 'Caixa de gordura',
                        'Dedetização' => 'Dedetização',
                        'Teste de Mangueiras de incêndio' => 'Teste de Mangueiras de incêndio',
                        'Pluvial' => 'Pluvial',
                        'Calhas' => 'Calhas',
                        'Gerador' => 'Gerador',
                        'Teste de água (ph, etc)' => 'Teste de água (ph, etc)',
                        'Iluminação de emergência' => 'Iluminação de emergência',
                        'Teste alarme de incêndio / sprinter' => 'Teste alarme de incêndio / sprinter',
                        'Vistoria playground' => 'Vistoria playground',
                        'Manutenção periódica Elevador' => 'Manutenção periódica Elevador',
                        'Aquecedores' => 'Aquecedores',
                        'Manutenção de ar condicionado' => 'Manutenção de ar condicionado',
                        'Pintura fachada' => 'Pintura fachada',
                        'Hidrantes' => 'Hidrantes',
                        'Inventário – Ativo imobilizado' => 'Inventário – Ativo imobilizado',
                        'Outro' => 'Outro',
                    ],
                ],
                [
                    'name' => 'outro',
                    'label' => 'Outro (se aplicável)',
                    'type' => 'text',
                    'required' => false,
                    'placeholder' => 'Descreva a manutenção',
                ],
                [
                    'name' => 'data_programada',
                    'label' => 'Data de programação',
                    'type' => 'text',
                    'required' => false,
                    'mask' => '99/99/9999',
                    'placeholder' => 'DD/MM/AAAA',
                ],
            ];

});

Artisan::command('schedule:run-dynamic {--force}', function (DynamicTaskCommandExecutor $executor) {

    if ((bool) $this->option('force')) {
        $this->info('Forçando a execução de todas as tarefas dinâmicas...');
        $executor->runAllNow();
        $this->info('Execução concluída.');

        return 0;
    }

    $this->info('Executando scheduler padrão para tarefas dinâmicas...');
    $exitCode = $this->call('schedule:run');

    return $exitCode;
})->purpose('Executa o scheduler carregando tarefas dinâmicas do banco');

$argv = $_SERVER['argv'] ?? [];
// Tenta encontrar o comando ignorando opções globais (ex: -v, --ansi)
$artisanCommand = collect($argv)
    ->slice(1)
    ->filter(fn($arg) => !str_starts_with($arg, '-'))
    ->first();

app(DynamicTaskCommandExecutor::class)->registerFromDatabase($artisanCommand);
