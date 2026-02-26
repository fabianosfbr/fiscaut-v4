<?php

namespace App\Services\Xml;

use App\Models\Issuer;
use App\Models\NotaFiscalServico;
use Exception;
use Illuminate\Support\Facades\Log;

class XmlNfseReaderService
{
    private $xml;

    private $simpleXml;

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
            if ($this->simpleXml->getName() !== 'CompNFe') {
                throw new Exception('XML inválido: Tag CompNFe não encontrada');
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
     * @return array
     *
     * @throws Exception
     */
    public function parse(): self
    {
        if (! $this->simpleXml) {
            throw new Exception('XML não foi carregado. Execute loadXml() primeiro.');
        }

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
            'xml' => base64_encode($this->xml),
        ];

        return $this;
    }

    /**
     * Salva ou atualiza os dados extraídos no banco de dados
     *
     * @param  array  $data  Dados opcionais para sobrescrever os dados do XML
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
