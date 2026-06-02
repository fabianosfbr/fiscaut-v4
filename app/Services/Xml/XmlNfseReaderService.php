<?php

namespace App\Services\Xml;

use App\Models\Issuer;
use App\Models\NotaFiscalServico;
use Exception;
use Illuminate\Support\Facades\Log;

class XmlNfseReaderService
{
    private string $xml;
    private string $xmlPlain;

    private ?\SimpleXMLElement $simpleXml = null;

    private array $data = [];

    private Issuer $issuer;

    /**
     * Carrega e valida o XML
     *
     * @param  string  $xml  String XML ou string compactada em gzip
     *
     * @throws Exception
     */
    public function loadXml(string $xml): self
    {
        $this->xmlPlain = $xml;
        try {
            // Verifica se o conteúdo está compactado em gzip
            $decoded = @gzdecode(base64_decode($xml));
            if ($decoded !== false) {
                $this->xml = $decoded;
            } else {
                $this->xml = $xml;
            }

            // Carrega o XML usando SimpleXML
            $this->simpleXml = simplexml_load_string($this->xml);
            if ($this->simpleXml === false) {
                throw new Exception('Falha ao carregar XML: XML mal formatado');
            }

            // Valida se é um XML de NFSe válido verificando a tag raiz
            $rootName = $this->simpleXml->getName();
            if (! in_array($rootName, ['CompNFe', 'NFSe'])) {
                throw new Exception('XML inválido: Tag raiz "'.$rootName.'" não reconhecida para NFSe');
            }

            return $this;
        } catch (Exception $e) {
            Log::error('Erro ao carregar XML: '.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Extrai e mapeia os dados do XML para um array estruturado
     *
     *
     * @throws Exception
     */
    public function parse(): self
    {
        if ($this->simpleXml === null) {
            throw new Exception('XML não foi carregado. Execute loadXml() primeiro.');
        }

        $rootName = $this->simpleXml->getName();

        if ($rootName === 'CompNFe') {
            $this->parseCompNFe();
        } elseif ($rootName === 'NFSe') {
            $this->parseNFSe();
        }

        return $this;
    }

    /**
     * Parse do formato CompNFe (padrão antigo)
     */
    private function parseCompNFe(): void
    {
        $nfe = $this->simpleXml->NFe;

        $this->data = [
            'numero' => (string) $nfe->NumeroNFe,
            'codigo_verificacao' => (string) $nfe->CodigoVerificador,
            'data_emissao' => str_replace('T', ' ', (string) $nfe->DataEmissaoNFe),
            'data_competencia' => (string) $nfe->DataCompetenciaNFe,
            'codigo_servico' => (string) $nfe->CodigoServicoMunicipal,
            'descricao_servico' => (string) $nfe->DescricaoServicoMunicipal,
            'serie_rps' => (string) $nfe->SerieRPS,
            'numero_rps' => (string) $nfe->NumeroRPS,
            'valor_servico' => (float) $nfe->ValorNFe,
            'base_calculo' => (float) $nfe->BaseCalculoISS,
            'aliquota_iss' => (float) $nfe->AliquotaIss,
            'valor_iss' => (float) $nfe->ValorISS,
            'valor_liquido' => (float) $nfe->ValorLiquidoNFe,
            'discriminacao' => (string) $nfe->Discriminacao,

            'prestador' => [
                'cnpj' => (string) $nfe->Prestador->CnpjCpf,
                'inscricao_municipal' => (string) $nfe->Prestador->InscricaoMunicipal,
                'razao_social' => (string) $nfe->Prestador->RazaoSocialNome,
                'email' => (string) $nfe->Prestador->Email,
                'endereco' => [
                    'logradouro' => (string) $nfe->Prestador->Logradouro,
                    'numero' => (string) $nfe->Prestador->Numero,
                    'complemento' => (string) $nfe->Prestador->Complemento,
                    'bairro' => (string) $nfe->Prestador->Bairro,
                    'municipio_id' => (string) $nfe->Prestador->MunicipioId,
                    'uf' => (string) $nfe->Prestador->UfSigla,
                    'cep' => (string) $nfe->Prestador->Cep,
                ],
            ],

            'tomador' => [
                'cnpj' => (string) $nfe->Tomador->CnpjCpf,
                'inscricao_municipal' => (string) $nfe->Tomador->InscricaoMunicipal,
                'razao_social' => (string) $nfe->Tomador->RazaoSocialNome,
                'municipio_id' => (string) $nfe->Tomador->MunicipioId,
                'municipio' => (string) $nfe->Tomador->Municipio,
                'uf' => (string) $nfe->Tomador->UfSigla,
            ],
            'xml' => $this->xmlPlain,
        ];
    }

    /**
     * Parse do formato NFSe (novo padrão SIEG com DPS)
     */
    private function parseNFSe(): void
    {
        $namespaces = $this->simpleXml->getNamespaces(true);
        $ns = $namespaces[''] ?? null;

        $root = $ns
            ? $this->simpleXml->children($ns)
            : $this->simpleXml;

        $infNFSe = $root->infNFSe;

        if (empty($infNFSe)) {
            throw new Exception('Tag infNFSe não encontrada no XML NFSe');
        }

        // Dados do emitente (nível do infNFSe)
        $emit = $infNFSe->emit;
        $emitCNPJ = (string) ($emit->CNPJ ?? '');
        $emitNome = (string) ($emit->xNome ?? '');
        $emitIM = (string) ($emit->IM ?? '');

        // Dados do DPS (Documento de Prestação de Serviço)
        $infDPS = null;
        if ($ns && isset($infNFSe->DPS)) {
            $infDPS = $infNFSe->DPS->children($ns)->infDPS;
        } elseif (isset($infNFSe->DPS)) {
            $infDPS = $infNFSe->DPS->infDPS;
        }

        // Dados do tomador (dentro do DPS)
        $tomCNPJ = '';
        $tomNome = '';
        $tomMunId = '';
        $tomMun = '';

        if (! empty($infDPS)) {
            $toma = $infDPS->toma;
            $tomCNPJ = (string) ($toma->CNPJ ?? '');
            $tomNome = (string) ($toma->xNome ?? '');

            // Município do tomador (endereço)
            if (isset($toma->end)) {
                $endNac = $toma->end->endNac ?? $toma->end;
                $tomMunId = (string) ($endNac->cMun ?? '');
            }

            // Dados do serviço
            $serv = $infDPS->serv;
            $locPrest = $serv->locPrest ?? null;

            // Dados de valores
            $valores = $infDPS->valores;
            $vServ = (float) ($valores->vServPrest->vServ ?? 0);

            // Dados de tributação
            $tribMun = $valores->trib->tribMun ?? null;
        }

        // Local de incidência (nível do infNFSe)
        $cLocIncid = (string) ($infNFSe->cLocIncid ?? '');
        $xLocIncid = (string) ($infNFSe->xLocIncid ?? '');
        $xLocEmi = (string) ($infNFSe->xLocEmi ?? '');

        // Valores no nível infNFSe
        $valoresNFSe = $infNFSe->valores;
        $vBC = (float) ($valoresNFSe->vBC ?? 0);
        $pAliqAplic = (float) ($valoresNFSe->pAliqAplic ?? 0);
        $vTotalRet = (float) ($valoresNFSe->vTotalRet ?? 0);
        $vLiq = (float) ($valoresNFSe->vLiq ?? 0);

        // Dados do DPS se disponíveis
        $dhEmi = $infDPS ? (string) $infDPS->dhEmi : (string) ($infNFSe->dhProc ?? '');
        $dCompet = $infDPS ? (string) $infDPS->dCompet : '';
        $serie = $infDPS ? (string) $infDPS->serie : '';
        $nDPS = $infDPS ? (string) $infDPS->nDPS : '';
        $nNFSe = (string) ($infNFSe->nNFSe ?? '');
        $nDFSe = (string) ($infNFSe->nDFSe ?? '');

        // Código de serviço e descrição
        $cTribNac = '';
        $cTribMun = '';
        $xDescServ = '';

        if (! empty($infDPS) && isset($infDPS->serv->cServ)) {
            $cServ = $infDPS->serv->cServ;
            $cTribNac = (string) ($cServ->cTribNac ?? '');
            $cTribMun = (string) ($cServ->cTribMun ?? '');
            $xDescServ = (string) ($cServ->xDescServ ?? '');
        }

        $this->data = [
            'numero' => $nNFSe ?: $nDPS,
            'codigo_verificacao' => $nDFSe,
            'data_emissao' => str_replace('T', ' ', $dhEmi),
            'data_competencia' => $dCompet,
            'codigo_servico' => $cTribMun,
            'descricao_servico' => $xDescServ,
            'serie_rps' => $serie,
            'numero_rps' => $nDPS,
            'valor_servico' => $vBC > 0 ? $vBC : $vLiq,
            'base_calculo' => $vBC,
            'aliquota_iss' => $pAliqAplic,
            'valor_iss' => ($vBC * $pAliqAplic / 100),
            'valor_liquido' => $vLiq,
            'discriminacao' => $xDescServ,

            'prestador' => [
                'cnpj' => $emitCNPJ,
                'inscricao_municipal' => $emitIM,
                'razao_social' => $emitNome,
                'email' => (string) ($emit->email ?? ''),
                'endereco' => [
                    'logradouro' => (string) ($emit->enderNac->xLgr ?? ''),
                    'numero' => (string) ($emit->enderNac->nro ?? ''),
                    'complemento' => (string) ($emit->enderNac->xCpl ?? ''),
                    'bairro' => (string) ($emit->enderNac->xBairro ?? ''),
                    'municipio_id' => $cLocIncid,
                    'uf' => (string) ($emit->enderNac->UF ?? ''),
                    'cep' => (string) ($emit->enderNac->CEP ?? ''),
                ],
            ],

            'tomador' => [
                'cnpj' => $tomCNPJ,
                'razao_social' => $tomNome,
                'municipio_id' => $tomMunId,
            ],
            'xml' => $this->xmlPlain,
        ];
    }

    /**
     * Salva ou atualiza os dados extraídos no banco de dados
     */
    public function save(): NotaFiscalServico
    {
        return NotaFiscalServico::updateOrCreate(
            [
                'numero' => $this->data['numero'],
                'prestador_cnpj' => $this->data['prestador']['cnpj'],
            ],
            [
                'data_emissao' => $this->data['data_emissao'],
                'chave' => $this->data['chave'] ?? $this->data['codigo_verificacao'] ?? null,
                'chave_acesso' => $this->data['chave'] ?? $this->data['codigo_verificacao'] ?? null,
                'issuer_id' => $this->issuer->id,
                'valor_servico' => $this->data['valor_servico'],
                'tomador_servico' => $this->data['tomador']['razao_social'],
                'codigo_servico' => $this->data['codigo_servico'],
                'descricao_servico' => $this->data['descricao_servico'],
                'tomador_cnpj' => $this->data['tomador']['cnpj'],
                'prestador_servico' => $this->data['prestador']['razao_social'],
                'prestador_cnpj' => $this->data['prestador']['cnpj'],
                'prestador_im' => $this->data['prestador']['inscricao_municipal'],
                'xml' => $this->data['xml'],
            ]
        );
    }

    /**
     * Retorna os dados extraídos do XML
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * Define o emissor (issuer) para o qual a NFSe pertence
     */
    public function setIssuer(Issuer $issuer): self
    {
        $this->issuer = $issuer;

        return $this;
    }
}
