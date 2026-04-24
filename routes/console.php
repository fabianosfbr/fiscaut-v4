<?php

use App\Console\Scheduling\DynamicTaskCommandExecutor;
use App\Jobs\SendCobrancaEmailJob;
use App\Models\Issuer;
use App\Models\SuperLogicaUnidade;
use Illuminate\Support\Facades\Artisan;

Artisan::command('play', function () {

    $issuer = Issuer::find(62);

    $service = new \App\Services\SuperlogicaConnectionService($issuer);

    $inadimplencias = $service
        ->receita()
        ->listarInadimplencia([
            'idCondominio' => $issuer->superlogica_condominio_id,
        ]);

    foreach ($inadimplencias as $record) {

        foreach ($record['recebimento'] as $recb) {

            $vencimentoStr = data_get($recb, 'dt_vencimento_recb');
            $diasAtraso = data_get($recb, 'encargos.0.diasatraso');
            ds($vencimentoStr);
            ds($diasAtraso);
            dd($recb);
        }
    }

    dd('parei');

    $unidade = SuperLogicaUnidade::find(800);
    //   dd($unidade->metadados);

    $mapa = [
        'st_unidade_uni' => 'numero_unidade',
        'st_bloco_uni' => 'bloco_quadra',
        'st_sacado_uni' => 'nome_morador',
    ];

    $unidadeData = [];
    foreach ($mapa as $chaveOriginal => $chaveNova) {
        if (isset($unidade->metadados[$chaveOriginal])) {
            $unidadeData[$chaveNova] = $unidade->metadados[$chaveOriginal];
        }
    }

    SendCobrancaEmailJob::dispatch($issuer->id, 'contato@fabianofernandes.adm.br;gerencia.cont@speedgrupo.com.br', $unidadeData);

    dd('enviado');

    $service = new \App\Services\SuperlogicaConnectionService($issuer);
    $condominios = $service
        ->despesa()
        ->listarFornecedor([
            'contatosDoTipo' => 'fornecedores',
            'itensPorPagina' => 50,
            'pagina' => 1,
        ]);

    dd($condominios[0]);

    $unidades = $service->unidade()->listar([
        'idCondominio' => 237,
        'exibirDadosDosContatos' => 1,
        'exibirGruposDasUnidades' => 1,
        'exibirInadimplencia' => 1,
        'pagina' => 1,
    ]);

    dd(count($unidades));

    dd($issuer->tenant()->first()->superlogica_base_url);
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
    ->filter(fn ($arg) => ! str_starts_with($arg, '-'))
    ->first();

app(DynamicTaskCommandExecutor::class)->registerFromDatabase($artisanCommand);
