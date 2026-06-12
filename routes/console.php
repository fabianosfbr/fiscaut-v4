<?php

use App\Console\Scheduling\DynamicTaskCommandExecutor;
use App\Models\Issuer;
use App\Services\Xml\XmlNfseReaderService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

Artisan::command('play', function () {
    $issuer = Issuer::find(18);
    $xml = '<?xml version="1.0" encoding="utf-8"?><NFSe versao="1.01" xmlns="http://www.sped.fazenda.gov.br/nfse"><infNFSe Id="NFS33045572213426116000139000000000001726061434996709"><xLocEmi>Rio de Janeiro</xLocEmi><xLocPrestacao>Rio de Janeiro</xLocPrestacao><nNFSe>17</nNFSe><cLocIncid>3304557</cLocIncid><xLocIncid>Rio de Janeiro</xLocIncid><xTribNac>Representação de qualquer natureza, inclusive comercial.</xTribNac><xTribMun>Representação comercial.</xTribMun><xNBS>Serviços de intermediação na distribuição de mercadorias</xNBS><verAplic>EmissorWeb_1.6.0.0</verAplic><ambGer>2</ambGer><tpEmis>1</tpEmis><procEmi>2</procEmi><cStat>100</cStat><dhProc>2026-06-11T10:42:40-03:00</dhProc><nDFSe>91161344</nDFSe><emit><CNPJ>13426116000139</CNPJ><xNome>A RIO ES SOLUCOES EM LOGISTICA LTDA</xNome><enderNac><xLgr>AVENIDA DAS AMERICAS</xLgr><nro>8505</nro><xBairro>BARRA DA TIJUCA</xBairro><cMun>3304557</cMun><UF>RJ</UF><CEP>22793081</CEP></enderNac><fone>2124123572</fone><email>jjopinto@yahoo.com.br</email></emit><valores><vLiq>12392.26</vLiq></valores><DPS versao="1.01" xmlns="http://www.sped.fazenda.gov.br/nfse"><infDPS Id="DPS330455721342611600013970000000000000000014"><tpAmb>1</tpAmb><dhEmi>2026-06-11T10:42:40-03:00</dhEmi><verAplic>EmissorWeb_1.6.0.0</verAplic><serie>70000</serie><nDPS>14</nDPS><dCompet>2026-06-11</dCompet><tpEmit>1</tpEmit><cLocEmi>3304557</cLocEmi><prest><CNPJ>13426116000139</CNPJ><fone>2124094595</fone><email>jjopinto@yahoo.com.br</email><regTrib><opSimpNac>3</opSimpNac><regApTribSN>1</regApTribSN><regEspTrib>0</regEspTrib></regTrib></prest><toma><CNPJ>10251329000189</CNPJ><xNome>KOPRON DO BRASIL COMERCIO E INDUSTRIA DE EQUIPAMENTOS DE LOGISTICA LTDA.</xNome><end><endNac><cMun>3524006</cMun><CEP>13295454</CEP></endNac><xLgr>VICE-PREFEITO HERMENEGILDO TONOLLI</xLgr><nro>2995</nro><xCpl>GALPAO3</xCpl><xBairro>SAO ROQUE DA CHAVE</xBairro></end></toma><serv><locPrest><cLocPrestacao>3304557</cLocPrestacao></locPrest><cServ><cTribNac>100901</cTribNac><cTribMun>001</cTribMun><xDescServ>COMISSÃO REFERENTE 01/05/2026 Á 31/05/2026 - VENCIMENTO : 15/06/2026 BANCO DO BRASIL(001) AGÊNCIA 2909-2 - CONTA CORRENTE: 61.425-4</xDescServ><cNBS>102010000</cNBS></cServ></serv><valores><vServPrest><vServ>12392.26</vServ></vServPrest><trib><tribMun><tribISSQN>1</tribISSQN><tpRetISSQN>1</tpRetISSQN></tribMun><tribFed><piscofins><CST>00</CST></piscofins></tribFed><totTrib><pTotTrib><pTotTribFed>13.45</pTotTribFed><pTotTribEst>0.00</pTotTribEst><pTotTribMun>4.92</pTotTribMun></pTotTrib></totTrib></trib></valores></infDPS></DPS></infNFSe><Signature xmlns="http://www.w3.org/2000/09/xmldsig#"><SignedInfo><CanonicalizationMethod Algorithm="http://www.w3.org/2001/10/xml-exc-c14n#WithComments" /><SignatureMethod Algorithm="http://www.w3.org/2001/04/xmldsig-more#rsa-sha256" /><Reference URI="#NFS33045572213426116000139000000000001726061434996709"><Transforms><Transform Algorithm="http://www.w3.org/2000/09/xmldsig#enveloped-signature" /><Transform Algorithm="http://www.w3.org/2001/10/xml-exc-c14n#WithComments" /></Transforms><DigestMethod Algorithm="http://www.w3.org/2001/04/xmlenc#sha256" /><DigestValue>S5DELe0lbx4JT4XbWdRmgCsDi+HahjEt8Qkzf+ZF7cA=</DigestValue></Reference></SignedInfo><SignatureValue>TDH6aVyIchPnku7USpO4Ma1n7XhpbsoxMI5/vIzH6MryfK+8GVix5ngvbS5GHiqEUF0WfKAXsOlTxr3dPIc7zh5VfaaAjLxnuUiI3Jd3gO8Q6ewFCjGRlMBmd3KoJTAB7O90Ra52/MGZy49TX/Sq69nz2vrYAIjCCC067/PoLbRszO2h3DpDSxoefidWUdrTLD146NByWwotKOHhQLGpT+bxqRnRJSHFmp/VarvW+TYXYwmcTF8a6TaOekJbgwnCFHf1bI2L2ZrhyeIex1noMIcAo5urmxw9+OYc92I1V5nco1ZNFvgVG3mTHjAmwCF3Lc7CAnpFNu99dBIXyklUiQ==</SignatureValue><KeyInfo><X509Data><X509Certificate>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</X509Certificate></X509Data></KeyInfo></Signature></NFSe>';

        
    (new XmlNfseReaderService)
        ->loadXml($xml)
        ->setIssuer($issuer)
        ->parse()
        ->save();
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
