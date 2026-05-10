<?php

use App\Console\Scheduling\DynamicTaskCommandExecutor;
use App\Jobs\SendCobrancaEmailJob;
use App\Models\Issuer;
use App\Models\SuperLogicaUnidade;
use App\Services\SuperlogicaConnectionService;
use App\Services\Xml\XmlIdentifierService;
use App\Services\Xml\XmlNfeReaderService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;

Artisan::command('play', function () {


    $xmlContent = '<resNFe xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" versao="1.01" xmlns="http://www.portalfiscal.inf.br/nfe"><chNFe>15260519290945000202550010000560801200136050</chNFe><CNPJ>19290945000202</CNPJ><xNome>FERMAQUINAS - PARAFUSOS - FILIAL 02</xNome><IE>155843648</IE><dhEmi>2026-05-08T12:00:57-03:00</dhEmi><tpNF>1</tpNF><vNF>3870.00</vNF><digVal>jGG0K2i0ovO3XSQ2UX/wtCM8Pig=</digVal><dhRecbto>2026-05-08T12:02:24-03:00</dhRecbto><nProt>215260024615128</nProt><cSitNFe>1</cSitNFe></resNFe>';

    $serviceNfe = app(XmlNfeReaderService::class)->loadXml($xmlContent);
    dd($serviceNfe->parse());

    $issuer = Issuer::find(62);

    $diasConfig = [20];

    $service = new SuperlogicaConnectionService($issuer);

    $inadimplencias = $service
        ->receita()
        ->listarInadimplencia([
            'idCondominio' => $issuer->superlogica_condominio_id,
        ]);

    foreach ($inadimplencias as $record) {

        $titulosAtrasados = [];

        foreach ($record['recebimento'] as $recb) {

            $vencimentoStr = data_get($recb, 'dt_vencimento_recb');
            if (! $vencimentoStr) {
                continue;
            }

            try {
                $vencimento = Carbon::createFromFormat('m/d/Y H:i:s', $vencimentoStr)->startOfDay();
            } catch (Exception $e) {
                try {
                    $vencimento = Carbon::parse($vencimentoStr)->startOfDay();
                } catch (Exception $e) {
                    continue;
                }
            }

            $diasAtraso = data_get($recb, 'encargos.0.diasatraso');

            $valor = number_format((float) data_get($recb, 'encargos.0.valorcorrigido', data_get($recb, 'vl_emitido_recb', 0)), 2, ',', '.');
            $titulosAtrasados[] = "Vencimento: {$vencimento->format('d/m/Y')} - Valor: R$ {$valor}";

            if (in_array((string) $diasAtraso, $diasConfig)) {
                $deveNotificar = true;
            }
        }

        $email = data_get($record, 'recebimento.0.contatosunidade.0.proprietario.0.email');

        if (! $email) {
            $idUnidade = data_get($record, 'id_unidade_uni') ?? data_get($record, 'st_unidade_uni');
            $unidade = SuperLogicaUnidade::where('id_unidade_uni', $idUnidade)
                ->where('id_condominio', $this->issuer->superlogica_condominio_id)
                ->first();

            $email = $unidade ? data_get($unidade, 'metadados.email_proprietario') : null;
        }

        if ($email) {
            $titulosHtml = '<ul>';
            foreach ($titulosAtrasados as $t) {
                $titulosHtml .= "<li>{$t}</li>";
            }
            $titulosHtml .= '</ul>';

            $unidadeData = [
                'numero_unidade' => data_get($record, 'st_unidade_uni', ''),
                'bloco_quadra' => data_get($record, 'st_bloco_uni', ''),
                'nome_morador' => data_get($record, 'st_sacado_uni', ''),
                'titulos_aberto' => $titulosHtml,
                'id_condominio_cond' => data_get($recb, 'id_condominio_cond'),
                'id_recebimento_recb' => data_get($recb, 'id_recebimento_recb'),
                'id_unidade_uni' => data_get($recb, 'id_unidade_uni'),
                'recebimento' => $recb,

            ];

            SendCobrancaEmailJob::dispatch($issuer->id, 'conib40105@inreur.com', $unidadeData);
            // sleep(10);

            dd($unidadeData);
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

    $service = new SuperlogicaConnectionService($issuer);
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
    ->filter(fn($arg) => ! str_starts_with($arg, '-'))
    ->first();

app(DynamicTaskCommandExecutor::class)->registerFromDatabase($artisanCommand);
