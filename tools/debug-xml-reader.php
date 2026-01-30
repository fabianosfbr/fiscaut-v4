<?php

require __DIR__.'/../vendor/autoload.php';

use App\Services\Xml\XmlReaderService;

$basePath = realpath(__DIR__.'/..');

$paths = [
    $basePath.'/xml-cte.xml',
    $basePath.'/xml-nfe.xml',
];

$service = new XmlReaderService;

foreach ($paths as $path) {
    echo "=== {$path} ===\n";

    if (! is_readable($path)) {
        echo "Arquivo não encontrado ou não legível.\n\n";

        continue;
    }

    $xml = file_get_contents($path);
    if ($xml === false) {
        echo "Falha ao ler o arquivo.\n\n";

        continue;
    }

    try {
        $data = $service->read($xml);
    } catch (Throwable $e) {
        echo "Erro ao parsear XML: {$e->getMessage()}\n\n";

        continue;
    }

    $rootName = array_key_first($data);
    echo "root: {$rootName}\n";

    if ($rootName === 'cteProc') {
        $cteProc = $data['cteProc'] ?? [];
        $infCte = $cteProc['CTe']['infCte'] ?? [];
        $infDoc = $infCte['infCTeNorm']['infDoc'] ?? [];

        $ide = $data['cteProc']['CTe']['infCte']['ide'] ?? [];
        $emit = $data['cteProc']['CTe']['infCte']['emit'] ?? [];
        echo 'nCT: '.($ide['nCT'] ?? 'null')."\n";
        echo 'tpCTe: '.($ide['tpCTe'] ?? 'null')."\n";
        echo 'dhEmi: '.($ide['dhEmi'] ?? 'null')."\n";
        echo 'emit CNPJ: '.($emit['CNPJ'] ?? $emit['CPF'] ?? 'null')."\n";
        echo 'itens ide keys: '.implode(',', array_keys($ide))."\n";
    }

    if ($rootName === 'nfeProc') {
        $ide = $data['nfeProc']['NFe']['infNFe']['ide'] ?? [];
        $emit = $data['nfeProc']['NFe']['infNFe']['emit'] ?? [];
        $prot = $data['nfeProc']['protNFe']['infProt'] ?? [];
        $det = $data['nfeProc']['NFe']['infNFe']['det'] ?? null;
        $detCount = is_array($det) ? (array_is_list($det) ? count($det) : 1) : 0;
        echo 'nNF: '.($ide['nNF'] ?? 'null')."\n";
        echo 'dhEmi: '.($ide['dhEmi'] ?? 'null')."\n";
        echo 'emit CNPJ: '.($emit['CNPJ'] ?? $emit['CPF'] ?? 'null')."\n";
        echo 'chNFe: '.($prot['chNFe'] ?? 'null')."\n";
        echo "det count: {$detCount}\n";
    }

    echo 'top-level keys: '.implode(',', array_keys($data[$rootName] ?? []))."\n";
    echo "\n";
}
