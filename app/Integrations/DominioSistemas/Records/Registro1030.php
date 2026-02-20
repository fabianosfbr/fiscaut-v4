<?php

namespace App\Integrations\DominioSistemas\Records;

use App\Models\Issuer;
use App\Models\NotaFiscalEletronica;
use App\Models\Tagged;

/**
 * Registro 1030 - Notas Fiscais de Entrada - Estoque
 */
class Registro1030 extends RegistroBase
{
    private array $produto;

    private ?string $cfopEquivalente;

    private NotaFiscalEletronica $notaFiscal;

    private Issuer $issuer;

    private Tagged $tagged;

    public function __construct(
        NotaFiscalEletronica $notaFiscal,
        array $produto,
        Tagged $tagged,
        Issuer $issuer,
        ?string $cfopEquivalente = null
    ) {
        $this->notaFiscal = $notaFiscal;
        $this->produto = $produto;
        $this->tagged = $tagged;
        $this->issuer = $issuer;
        $this->cfopEquivalente = $cfopEquivalente;
    }

    public function getTipoRegistro(): string
    {
        return '1030';
    }

    public function converterParaLinhaTxt(): string
    {

        $isZeraIcms = $this->isZeraIcms($this->issuer, $this->tagged->tag_id);
        $isZeraIpi = $this->isZeraIpi($this->issuer, $this->tagged->tag_id);

        $impostos = $this->produto['impostos'] ?? [];
        $icmsData = $this->extrairDadosIcms($impostos);

        $campos = [];
        $campos[] = $this->getTipoRegistro(); // 1: Identificação do registro
        $campos[] = $this->formatarCampo($this->obterIdentificador($this->notaFiscal, $this->produto, $this->issuer->cnpj) ?? '', null, 'C'); // 2: Código do produto
        $campos[] = $this->formatarCampo($this->produto['qCom'] ?? 0, null, 'D'); // 3: Quantidade (2 decimais)
        $campos[] = $this->formatarCampo($this->produto['vProd'] ?? 0, null, 'D'); // 4: Valor total (Base Cal. + IPI)
        $campos[] = $this->formatarCampo($this->produto['impostos']['vIPI'] ?? 0, null, 'D2'); // 5: Valor IPI
        $campos[] = '0,00'; // 6: Base de cálculo
        $campos[] = '1'; // 7: Tipo de Lançamento (1=Produto vinculado a nota / 2=Lançamento Extra)
        $campos[] = $this->formatarCampo($this->notaFiscal->data_emissao, null, 'X'); // 8: Data (dd/mm/aaaa)
        $campos[] = '0,00'; // 9: Número da DI
        $campos[] = $isZeraIcms ? '090' : $this->converterCSTICMS($this->produto['impostos']['CST'] ?? ''); // 10: Código da Situação Tributária
        $campos[] = $this->formatarCampo($this->produto['vProd'] ?? 0, null, 'D'); // 11: Valor bruto do produto
        $campos[] = $this->formatarCampo($this->produto['vDesc'] ?? 0, null, 'D'); // 12: Valor do desconto
        $campos[] = $isZeraIcms ? '0,00' : $this->converterCSTICMS($this->produto['impostos']['vBC'] ?? ''); // 13: Base de cálculo do ICMS

        $campos[] = $this->formatarCampo($this->produto['impostos']['vBCFCPUFDest'] ?? '', null, 'N'); // 14: Base de cálculo do ICMS p/ Substituição Tributária
        $campos[] = $this->formatarCampo($this->produto['impostos']['pICMS'] ?? '', null, 'C'); // 15: Alíquota do ICMS
        $campos[] = 'N'; // 16: Produto Incentivado (S ou N. Apenas para o Estado de PE)
        $campos[] = '0,00'; // 17: Código da apuração (Apenas para o Estado de PE)

        $campos[] = $this->formatarCampo($this->produto['vFrete'] ?? '', null, 'N'); // 18: Valor do frete
        $campos[] = $this->formatarCampo($this->produto['vSeg'] ?? '', null, 'N'); // 19: Valor do seguro
        $campos[] = $this->formatarCampo($this->produto['vOutro'] ?? 0, null, 'D2'); // 20: Valor das despesas acessórias

        $campos[] = $this->formatarCampo($icmsData['qBCMonoRet'] ?? 0, null, 'D3'); // 21: Quantidade de gasolina (3 decimais)
        $campos[] = $this->formatarCampo($icmsData['vICMS'] ?? 0, null, 'D2'); // 22: Valor do ICMS
        $campos[] = $this->formatarCampo($icmsData['vICMSST'] ?? 0, null, 'D2'); // 23: Valor da SUBTRI

        // IPI
        $ipiData = $this->extrairDadosIpi($impostos);
        $campos[] = '0,00'; // 24: Valor de isentas IPI

        // PIS
        $pisData = $this->extrairDadosPis($impostos);
        $campos[] = '0,00'; // 25: Valor de outras IPI

        // COFINS
        $cofinsData = $this->extrairDadosCofins($impostos);

        $campos[] = '0,00'; // 26: ICMS NFP
        $campos[] = $this->formatarCampo($this->produto['vUnCom'] ?? 0, null, 'D6'); // 27: Valor Unitário (6 decimais)
        $campos[] = '0,00'; // 28: Alíquota da Substituição Tributária
        $campos[] = $isZeraIpi ? '49' : $this->converterCSTIPI($ipiData['CST'] ?? ''); // 29: Código de Tributação do IPI
        $campos[] = $this->formatarCampo($ipiData['pIPI'] ?? '', null, 'D2'); // 30: Alíquota do IPI

        $campos[] = '0,00'; // 31: Base de cálculo ISSQN
        $campos[] = '0,00'; // 32: Alíquota do ISSQN
        $campos[] = '0,00'; // 33: Valor ISSQN
        $campos[] = $this->cfopEquivalente ?? ''; // 34: CFOP
        $campos[] = ''; // 35: Série de fabricação do ECF

        $campos[] = '0,00'; // 36: Alíquota do PIS (4 decimais)
        $campos[] = '0,00'; // 37: Valor do PIS
        $campos[] = '0,00'; // 38: Alíquota da COFINS
        $campos[] = '0,00'; // 39: Valor da COFINS

        $campos[] = '0,00'; // 40: Custo total do produto
        $campos[] = '50'; // 41: CST do PIS
        $campos[] = '0,00'; // 42: Base de cálculo do PIS
        $campos[] = '50'; // 43: CST da COFINS
        $campos[] = '0,00'; // 44: Base de cálculo da COFINS
        $campos[] = ''; // 45: Chassi do veículo
        $campos[] = '9'; // 46: Tipo de operação com o veículo
        $campos[] = ''; // 47: Lote do medicamento
        $campos[] = '0,00'; // 48: Quantidade de item por lote de medicamento
        $campos[] = $this->formatarCampo($this->notaFiscal->data_emissao ?? '', null, 'X'); // 49: Data de validade
        $campos[] = $this->formatarCampo($this->notaFiscal->data_emissao ?? '', null, 'X'); // 50: Data de fabricação do medicamento

        $campos[] = '00'; // 51: Referência base de cálculo
        $campos[] = '0,00'; // 52: Valor tabelado / máximo
        $campos[] = ''; // 53: Número de série da arma
        $campos[] = ''; // 54: Número de série do cano
        $campos[] = '999'; // 55: Enquadramento do IPI
        $campos[] = ''; // 56: Movimentação física do produto
        $campos[] = $this->formatarCampo($this->produto['uCom'] ?? 0, null, 'D2'); // 57: Unidade comercializada
        $campos[] = $this->cfopEquivalente; // 58: Complemento da CFOP - Portaria CAT nº 17/99
        $campos[] = '0'; // 59: Tanque do combustível
        $campos[] = $this->formatarCampo($this->produto['vProd'] ?? '', null, 'N'); // 60: Valor contábil produto
        $campos[] = '0,00'; // 61: Quantidade tributada PIS por unidade de medida

        $campos[] = '0,00'; // 62: Valor unidade PIS por unidade de medida
        $campos[] = '0,00'; // 63: Valor PIS por unidade de medida
        $campos[] = '0,00'; // 64: Quantidade tributada COFINS por unidade de medida
        $campos[] = '0,00'; // 65: Valor unidade COFINS por unidade de medida
        $campos[] = '0,00'; // 66: Valor COFINS por unidade de medida
        $campos[] = '01'; // 67: Base do crédito

        $campos[] = '0'; // 68: Data da DI (dd/mm/aaaa)
        $campos[] = ''; // 69: Número da adição
        $campos[] = ''; // 70: Sequencial da adição
        $campos[] = '0,00'; // 71: Desconto da DI

        $campos[] = '0,00'; // 72: Valor do IOF
        $campos[] = '0,00'; // 73: Valor das despesas aduaneiras
        $campos[] = $this->formatarCampo($this->produto['vSeg'] ?? 0, null, 'D2'); // 74: Valor do seguro (DI)
        $campos[] = $this->formatarCampo($this->produto['vFrete'] ?? 0, null, 'D2'); // 75: Valor do frete (DI)

        $campos[] = ''; // 76: Indicador de Receita
        $campos[] = ''; // 77: CNPJ do Fabricante
        $campos[] = ''; // 78: Código do Benefício Fiscal
        $campos[] = ''; // 79: Motivo da Desoneração do ICMS
        $campos[] = '0,00'; // 80: Valor do ICMS Desonerado
        $campos[] = '0,00'; // 81: Valor do Abatimento NT

        $campos[] = '0'; // 82: Código do Enquadramento do IPI
        $campos[] = '0'; // 83: Código do Selo de Controle IPI
        $campos[] = '0'; // 84: Quantidade do Selo de Controle IPI
        $campos[] = '0,00'; // 85: Classificação de Item de Energia Elétrica/Telecomunicação
        $campos[] = '0,00'; // 86: Valor contábil produto

        $campos[] = '0,00'; // 87: Quantidade tributada PIS por unidade de medida (3 decimais)
        $campos[] = '0,00'; // 88: Valor da unidade de medida PIS (4 decimais)
        $campos[] = '0,00'; // 89: Valor PIS por unidade de medida
        $campos[] = ''; // 90: Quantidade tributada COFINS por unidade de medida (3 decimais)
        $campos[] = '0,0000'; // 91: Valor unidade COFINS por unidade de medida (4 decimais)
        $campos[] = '0,00'; // 92: Valor COFINS por unidade de medida

        $campos[] = ''; // 93: Base do crédito (01=Revenda; 02=Insumo; 03=Serv.Insumo; etc.)
        $campos[] = ''; // 94: Número da Nota/Redução Z/Cupom Fiscal devolvido
        $campos[] = $this->obterIdentificador($this->notaFiscal, $this->produto, $this->issuer->cnpj); // 95: Descrição complementar
        $campos[] = '0,00'; // 96: Nota devolvida – CST PIS
        $campos[] = '0,00'; // 97: Nota devolvida – CST COFINS
        $campos[] = '0,00'; // 98: Vínculo de Crédito PIS
        $campos[] = '0,00'; // 99: Vínculo de Crédito COFINS
        $campos[] = '0,00'; // 100: Exclusão PIS
        $campos[] = '0,00'; // 101: Exclusão COFINS

        $campos[] = '0,00'; // 102: Base de cálculo - ICMS Carga Média
        $campos[] = '0,00'; // 103: Alíquota - ICMS Carga Média
        $campos[] = '0,00'; // 104: Valor - ICMS Carga Média
        $campos[] = '0,00'; // 105: Número de série da máquina ECF - Documento devolvido
        $campos[] = '0,00'; // 106: PIS/COFINS - Percentual de redução na base de cálculo

        $campos[] = '0,00'; // 107: Código de recolhimento PIS – Nota devolvida
        $campos[] = '0,00'; // 108: Código de recolhimento COFINS – Nota devolvida
        $campos[] = '0,00'; // 109: Código de recolhimento PIS
        $campos[] = '0,00'; // 110: Código de recolhimento COFINS
        $campos[] = '0,00'; // 111: Crédito Presumido PIS/COFINS - PIS
        $campos[] = '0,00'; // 112: Crédito Presumido PIS/COFINS - COFINS

        $campos[] = '0,00'; // 113: ICMS ST Antecipação Total - Base de cálculo
        $campos[] = '0,00'; // 114: ICMS ST Antecipação Total - Alíquota
        $campos[] = '0,00'; // 115: ICMS ST Antecipação Total - Valor
        $campos[] = '0,00'; // 116: Código de recolhimento – IPI

        $campos[] = $this->formatarCampo($this->produto['CEST'] ?? '', null, 'N'); // 117: Código CEST (conforme tabela CEST)

        $campos[] = '0,00'; // 118: ICMS ST Retido – Base de cálculo (DF, RJ, MG, SC, RS e SP)
        $campos[] = '0,00'; // 119: ICMS ST Retido – Valor (DF, RJ, MG, SC, RS e SP)
        $campos[] = '0,00'; // 120: ICMS ST Retido – Possui a tag no XML (S ou N. PR, MG, SC, RS e SP)
        $campos[] = ''; // 121: Identificador (máx. 60 caracteres)
        $campos[] = '0,00'; // 122: ICMS Próprio do Substituto – Valor (apenas PR)
        $campos[] = '0,00'; // 123: Valor Desonerado (NFe código 55, NFe Avulsa 55, NFCe 65)
        $campos[] = ''; // 124: Código (Tipo de Uso/Isenção) - 1=Táxi; 3=Produtor; 4=Frotista; etc.
        $campos[] = ''; // 125: Código (Dedução ICMS)

        $campos[] = '0,0000'; // 126: ICMS Monofásico Qtde Trib. (BA, CE, GO, MA, PI, RJ, SC, SE, TO)
        $campos[] = '0,0000'; // 127: ICMS Monofásico Alíq. Fixa (ad rem.) (BA, CE, GO, MA, PI, RJ, SC, SE, TO)
        $campos[] = '0,00'; // 128: ICMS Monofásico Valor (BA, CE, GO, MA, PI, RJ, SC, SE, TO)
        $campos[] = '0,0000'; // 129: ICMS Monofásico Fator de Correção do Volume (FCV) (apenas GO)

        $campos[] = ''; // 130: IBS - cClass Trib
        $campos[] = '0,00'; // 131: IBS - Base de cálculo
        $campos[] = '0,00'; // 132: IBS - Alíquota
        $campos[] = '0,00'; // 133: IBS - Valor

        $campos[] = ''; // 134: CBS - cClass Trib
        $campos[] = '0,00'; // 135: CBS - Base de cálculo
        $campos[] = '0,00'; // 136: CBS - Alíquota
        $campos[] = '0,00'; // 137: CBS - Valor

        return $this->montarLinha($campos);
    }

    private function extrairDadosIcms($imposto)
    {
        if (isset($imposto['ICMS'])) {
            foreach ($imposto['ICMS'] as $key => $value) {
                return $value; // Retorna o array do primeiro tipo de ICMS encontrado
            }
        }

        return [];
    }

    private function extrairDadosIpi($imposto)
    {
        if (isset($imposto['IPI']['IPITrib'])) {
            return $imposto['IPI']['IPITrib'];
        }

        return [];
    }

    private function extrairDadosPis($imposto)
    {
        if (isset($imposto['PIS'])) {
            foreach ($imposto['PIS'] as $key => $value) {
                return $value;
            }
        }

        return [];
    }

    private function extrairDadosCofins($imposto)
    {
        if (isset($imposto['COFINS'])) {
            foreach ($imposto['COFINS'] as $key => $value) {
                return $value;
            }
        }

        return [];
    }
}
