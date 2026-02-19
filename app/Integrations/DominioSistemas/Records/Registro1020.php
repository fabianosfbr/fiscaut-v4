<?php

namespace App\Integrations\DominioSistemas\Records;

use App\Models\Issuer;
use App\Models\Tagged;
use App\Models\GeneralSetting;
use App\Models\NotaFiscalEletronica;
use App\Models\EntradasImpostosEquivalente;

/**
 * Registro 1020 - Notas Fiscais de Entrada - Impostos
 * 
 * Este registro é filho do registro 1000 e contém os valores de impostos
 * da nota fiscal de entrada.
 * 
 * Deve ser gerado um registro 1020 para cada tipo de imposto:
 * - Código 1: ICMS
 * - Código 2: IPI
 * - Código 8: DIFAL
 */
class Registro1020 extends RegistroBase
{
    /**
     * Cache estático para armazenar as verificações de zera ICMS já consultadas
     * Key: issuer_id_tag_code
     * Value: bool
     *
     * @var array
     */
    private static array $zeraIcmsCache = [];

    /**
     * Cache estático para armazenar as verificações de zera IPI já consultadas
     * Key: issuer_id_tag_code
     * Value: bool
     *
     * @var array
     */
    private static array $zeraIpiCache = [];

    private Tagged $tagged;

    private NotaFiscalEletronica $notaFiscal;

    private Issuer $issuer;
    /**
     * Campo 2 - Código do imposto
     * 1 = ICMS
     * 2 = IPI
     * 8 = DIFAL
     */
    private int $codigoImposto;

    /**
     * Campo 3 - Percentual de redução da base de cálculo
     * Tipo: Decimal (2 casas)
     */
    private ?float $percentualReducaoBaseCalculo = null;

    /**
     * Campo 4 - Base de cálculo
     * Tipo: Decimal (2 casas)
     */
    private ?float $baseCalculo = null;

    /**
     * Campo 5 - Alíquota
     * Tipo: Decimal (2 ou 3 casas)
     * Imposto "39-IRRFP" - 3 casas decimais; Demais impostos - 2 casas decimais.
     */
    private ?float $aliquota = null;

    /**
     * Campo 6 - Valor do Imposto
     * Tipo: Decimal (2 casas)
     */
    private ?float $valorImposto = null;

    /**
     * Campo 7 - Valor de Isentas
     * Tipo: Decimal (2 casas)
     */
    private ?float $valorIsentas = null;

    /**
     * Campo 8 - Valor de Outras
     * Tipo: Decimal (2 casas)
     */
    private ?float $valorOutras = null;

    /**
     * Campo 9 - Valor do IPI
     * Tipo: Decimal (2 casas)
     */
    private ?float $valorIpi = null;

    /**
     * Campo 10 - Valor da substituição Tributária
     * Tipo: Decimal (2 casas)
     */
    private ?float $valorSubstituicaoTributaria = null;

    /**
     * Campo 11 - Valor Contábil
     * Tipo: Decimal (2 casas)
     */
    private ?float $valorContabil = null;

    /**
     * Campo 12 - Código do recolhimento do imposto
     * Tipo: Caractere
     */
    private ?string $codigoRecolhimentoImposto = null;

    /**
     * Campo 13 - Valor não tributadas
     * Tipo: Decimal (2 casas)
     * Apenas para o Estado de GO.
     */
    private ?float $valorNaoTributadas = null;

    /**
     * Campo 14 - Valor parcela reduzida
     * Tipo: Decimal (2 casas)
     * Apenas para o Estado de GO.
     */
    private ?float $valorParcelaReduzida = null;

    /**
     * Campo 15 - Alíquota Interestadual
     * Tipo: Numérico/Decimal
     * Para RS (Numérico): 1-4,00; 2-12,00; 3-4,00.
     * Para SP (Decimal): apenas 08-DIFALI e 27-ICMSA.
     */
    private ?string $aliquotaInterestadual = null;

    /**
     * Campo 16 - Natureza da renda
     * Tipo: Numérico
     * Limite 5 caracteres.
     * Apenas para: 16-IRRF, 22-PIS-RET, 23-COFINS-R, 24-CSOC-R, 25-CRF, 38-COSIRF, 39-IRRFP e 63-IRRF-APF.
     */
    private ?string $naturezaRenda = null;

    /**
     * Campo 17 - Tipo de Dedução
     * Tipo: Numérico
     * Limite 1 caractere.
     * Apenas para 63-IRRF-APF. 7=Por dependente, 8=Simplificada mensal.
     */
    private ?string $tipoDeducao = null;

    /**
     * Campo 18 - Tipo de Isenção
     * Tipo: Numérico
     * Limite 2 caracteres.
     * Apenas para 63-IRRF-APF.
     */
    private ?string $tipoIsencao = null;

    /**
     * Campo 19 - Descrição
     * Tipo: Caractere
     * Limite 100 caracteres.
     */
    private ?string $descricao = null;

    /**
     * Construtor do registro 1020
     * 
     * @param int $codigoImposto Código do imposto (1=ICMS, 2=IPI, 8=DIFAL)
     * @param array $valoresSegmento Valores do segmento da nota fiscal
     * @param NotaFiscalEletronica $notaFiscal Nota fiscal eletrônica
     * @param Tagged $tagged Tagged
     */
    public function __construct(
        int $codigoImposto,
        array $valoresSegmento,
        NotaFiscalEletronica $notaFiscal,
        Tagged $tagged,
        Issuer $issuer
    ) {
        $this->codigoImposto = $codigoImposto;
        $this->tagged = $tagged;
        $this->notaFiscal = $notaFiscal;
        $this->issuer = $issuer;

        // Calcula o valor contábil (valor dos produtos - desconto)
        $valorProdutos = (float) ($valoresSegmento['valor_produtos'] ?? 0);
        $valorDesconto = (float) ($valoresSegmento['valor_desconto'] ?? 0);
        $this->valorContabil = $valorProdutos - $valorDesconto;

        // Preenche os campos conforme o tipo de imposto
        $this->preencherCamposPorImposto($valoresSegmento, $notaFiscal);
    }

    /**
     * Preenche os campos do registro conforme o tipo de imposto
     * 
     * @param array $valoresSegmento
     * @param NotaFiscalEletronica $notaFiscal
     * @return void
     */
    private function preencherCamposPorImposto(array $valoresSegmento, NotaFiscalEletronica $notaFiscal): void
    {
        switch ($this->codigoImposto) {
            case 1: // ICMS
                $this->preencherIcms($valoresSegmento, $notaFiscal);
                break;
            case 2: // IPI
                $this->preencherIpi($valoresSegmento, $notaFiscal);
                break;
            case 8: // DIFAL
                $this->preencherDifal($valoresSegmento, $notaFiscal);
                break;
        }
    }

    /**
     * Preenche os campos para ICMS (Código 1)
     * 
     * Regras contábeis:
     * - Base de cálculo: valor_base_calculo do segmento
     * - Valor do imposto: valor_icms do segmento
     * - Valor de isentas: valor contábil quando não há valor de imposto (isenção)
     * - Valor de outras: para operações com outros benefícios fiscais
     * - Valor ST: valor_icms_st do segmento
     * 
     * @param array $valoresSegmento
     * @param NotaFiscalEletronica $notaFiscal
     * @return void
     */
    private function preencherIcms(array $valoresSegmento, NotaFiscalEletronica $notaFiscal): void
    {
        $isZeraIcms = $this->isZeraIcms();


        if (!is_null($valoresSegmento['CSOSN']) and !$isZeraIcms) {

            // $valoresTotais[8] += $valores['vProd'] * $percentual;
            // $valoresTotais[11] += $valores['vProd'] * $percentual;
        }

        if ($isZeraIcms) {

            // $valoresTotais[8] += $valorContabil - ($valorIPI + $valorST);
            // $valoresTotais[9] += $valorIPI;
            // $valoresTotais[10] += $valorST;
            // $valoresTotais[11] += $valorContabil;
        }

        if ($valoresSegmento['valor_cst'] == '00') {

            //Campo 4: Base de cálculo
            $this->baseCalculo = $valoresSegmento['valor_base_calculo'];

            //Campo 5: Alíquota
            $this->aliquota = $valoresSegmento['percentual_icms'];

            //Campo 6: Valor do imposto
            $this->valorImposto = $valoresSegmento['valor_icms'];

            //Campo 7: Valor de isentas
            $this->valorIsentas =  0;

            //Campo 8: Valor de outras
            $this->valorOutras =  $valoresSegmento['valor_produtos'];

            //Campo 9
            $this->valorIpi =  $valoresSegmento['valor_ipi'];

            //Campo 10
            $this->valorSubstituicaoTributaria =  0;

            //Campo 11
            $this->valorContabil = $valoresSegmento['valor_produtos'];

            // $valoresTotais[4] += $valores['vBcICMS']  * $percentual;
            // $valoresTotais[5] += $valores['pICMS'];
            // $valoresTotais[6] += $valores['vICMS'] * $percentual;

            // $valoresTotais[7] += 0;
            // $valoresTotais[8] += $valorContabil - ($valorIPI + $valorST + $valores['vBcICMS'] * $percentual);
            // $valoresTotais[9] += $valorIPI * $percentual;
            // $valoresTotais[10] += $valorST  * $percentual;
            // $valoresTotais[11] += $valorContabil;
        }

        if ($valoresSegmento['valor_cst'] == '10') {

            //Campo 4: Base de cálculo
            $this->baseCalculo = $valoresSegmento['valor_base_calculo'];

            //Campo 5: Alíquota
            $this->aliquota = $valoresSegmento['percentual_icms'];

            //Campo 6: Valor do imposto
            $this->valorImposto = $valoresSegmento['valor_icms'];

            //Campo 7: Valor de isentas
            $this->valorIsentas =  0;

            //Campo 8: Valor de outras
            $this->valorOutras =  $valoresSegmento['valor_produtos'];

            //Campo 9
            $this->valorIpi =  $valoresSegmento['valor_ipi'];

            //Campo 10
            $this->valorSubstituicaoTributaria =  0;

            //Campo 11
            $this->valorContabil = $valoresSegmento['valor_produtos'];

            // $opcaoCreditoIcms = self::validaOpcaoCreditoIcms($valores, $doc, $currentIssuer);

            // $valoresTotais[4] += $opcaoCreditoIcms ? $valores['vBcICMS'] * $percentual  : 0;
            // $valoresTotais[5] += $opcaoCreditoIcms ? $valores['pICMS'] : 0;
            // $valoresTotais[6] += $opcaoCreditoIcms ? $valores['vICMS'] * $percentual : 0;

            // $valoresTotais[8] += $opcaoCreditoIcms ? $valorContabil - ($valorIPI + $valores['vBcICMS'] * $percentual + $valores['vICMSST'] * $percentual)  : $valorContabil - ($valorIPI + $valores['vICMSST']);
            // $valoresTotais[9] +=  $valores['vIPI'] * $percentual;
            // $valoresTotais[10] += $valores['vICMSST'] * $percentual;
            // $valoresTotais[11] += $valorContabil;
        }

        if ($valoresSegmento['valor_cst'] == '20') {

            //Campo 4: Base de cálculo
            $this->baseCalculo = $valoresSegmento['valor_base_calculo'];

            //Campo 5: Alíquota
            $this->aliquota = $valoresSegmento['percentual_icms'];

            //Campo 6: Valor do imposto
            $this->valorImposto = $valoresSegmento['valor_icms'];


            //Campo 7: Valor de isentas
            $this->valorIsentas =  0;

            //Campo 8: Valor de outras
            $this->valorOutras =  $valoresSegmento['valor_produtos'];

            //Campo 9
            $this->valorIpi =  $valoresSegmento['valor_ipi'];

            //Campo 10
            $this->valorSubstituicaoTributaria =  0;

            //Campo 11
            $this->valorContabil = $valoresSegmento['valor_produtos'];

            // $valoresTotais[4] += $valores['vBcICMS'] * $percentual;
            // $valoresTotais[5] += $valores['pICMS'];
            // $valoresTotais[6] += $valores['vICMS'] * $percentual;
            // $valoresTotais[7] += $valorContabil - ($valores['vBcICMS'] * $percentual + $valores['vIPI'] * $percentual + $valores['vST']  * $percentual);
            // $valoresTotais[8] += 0;
            // $valoresTotais[9] += $valores['vIPI'] * $percentual;
            // $valoresTotais[10] += $valores['vST']  * $percentual;
            // $valoresTotais[11] += $valorContabil;
        }


        if ($valoresSegmento['valor_cst'] == '30' ||  $valoresSegmento['valor_cst'] == '70') {

            $tomaCreditoIcms = $this->tomaCreditoIcms();

            //Campo 4: Base de cálculo
            $this->baseCalculo = $valoresSegmento['valor_base_calculo'];

            //Campo 5: Alíquota
            $this->aliquota = $valoresSegmento['percentual_icms'];

            //Campo 6: Valor do imposto
            $this->valorImposto = $valoresSegmento['valor_icms'];


            //Campo 7: Valor de isentas
            $this->valorIsentas =  0;

            //Campo 8: Valor de outras
            $this->valorOutras =  $valoresSegmento['valor_produtos'];

            //Campo 9
            $this->valorIpi =  $valoresSegmento['valor_ipi'];

            //Campo 10
            $this->valorSubstituicaoTributaria =  0;

            //Campo 11
            $this->valorContabil = $valoresSegmento['valor_produtos'];


            // $valoresTotais[4] += $opcaoCreditoIcms ? $valores['vBcICMS'] * $percentual  : 0;
            // $valoresTotais[5] += $opcaoCreditoIcms ? $valores['pICMS'] : 0;
            // $valoresTotais[6] += $opcaoCreditoIcms ? $valores['vICMS'] * $percentual : 0;

            // $valoresTotais[7] += $opcaoCreditoIcms ? $valorContabil - ($valorIPI + $valores['vBcICMS'] * $percentual + $valores['vICMSST'] * $percentual)  : $valorContabil - ($valorIPI + $valores['vICMSST']);
            // $valoresTotais[9] +=  $valores['vIPI'] * $percentual;
            // $valoresTotais[10] += $valores['vICMSST'] * $percentual;
            // $valoresTotais[11] += $valorContabil;
        }

        if ($valoresSegmento['valor_cst'] == '40' ||  $valoresSegmento['valor_cst'] == '41') {

            //Campo 4: Base de cálculo
            $this->baseCalculo = $valoresSegmento['valor_base_calculo'];

            //Campo 5: Alíquota
            $this->aliquota = $valoresSegmento['percentual_icms'];

            //Campo 6: Valor do imposto
            $this->valorImposto = $valoresSegmento['valor_icms'];


            //Campo 7: Valor de isentas
            $this->valorIsentas =  0;

            //Campo 8: Valor de outras
            $this->valorOutras =  $valoresSegmento['valor_produtos'];

            //Campo 9
            $this->valorIpi =  $valoresSegmento['valor_ipi'];

            //Campo 10
            $this->valorSubstituicaoTributaria =  0;

            //Campo 11
            $this->valorContabil = $valoresSegmento['valor_produtos'];


            // $valoresTotais[4] += $valores['vBcICMS'] * $percentual;
            // $valoresTotais[5] = $valores['pICMS'];
            // $valoresTotais[6] += $valores['vICMS'] * $percentual;
            // $valoresTotais[7] += $valorContabil - ($valorIPI + $valores['vBcICMS'] * $percentual + $valores['vICMSST'] * $percentual);
            // $valoresTotais[8] += 0;
            // $valoresTotais[9] += $valores['vIPI']  * $percentual;
            // $valoresTotais[10] += $valores['vICMSST']  * $percentual;
            // $valoresTotais[11] += $valorContabil;
        }

        if ($valoresSegmento['valor_cst'] == '50' ||  $valoresSegmento['valor_cst'] == '51') {

            //Campo 4: Base de cálculo
            $this->baseCalculo = $valoresSegmento['valor_base_calculo'];

            //Campo 5: Alíquota
            $this->aliquota = $valoresSegmento['percentual_icms'];

            //Campo 6: Valor do imposto
            $this->valorImposto = $valoresSegmento['valor_icms'];


            //Campo 7: Valor de isentas
            $this->valorIsentas =  0;

            //Campo 8: Valor de outras
            $this->valorOutras =  $valoresSegmento['valor_produtos'];

            //Campo 9
            $this->valorIpi =  $valoresSegmento['valor_ipi'];

            //Campo 10
            $this->valorSubstituicaoTributaria =  0;

            //Campo 11
            $this->valorContabil = $valoresSegmento['valor_produtos'];


            // $valoresTotais[4] += $valores['vBcICMS'] * $percentual;
            // $valoresTotais[5] = $valores['pICMS'];
            // $valoresTotais[6] += $valores['vICMS'] * $percentual;
            // $valoresTotais[7] += 0;
            // $valoresTotais[8] += $valorContabil - ($valorIPI + $valores['vBcICMS'] * $percentual + $valores['vICMSST'] * $percentual);
            // $valoresTotais[9] += $valores['vIPI']  * $percentual;
            // $valoresTotais[10] += $valores['vICMSST']  * $percentual;
            // $valoresTotais[11] += $valorContabil;
        }

        if ($valoresSegmento['valor_cst'] == '60' ||  $valoresSegmento['valor_cst'] == '61' || $valoresSegmento['valor_cst'] == '90') {


            //Campo 07
            $this->valorIsentas =  0;

            //Campo 08
            $this->valorOutras =  $valoresSegmento['valor_produtos'];

            //Campo 09
            $this->valorIpi =  $valoresSegmento['valor_ipi'];

            //Campo 10
            $this->valorSubstituicaoTributaria =  0;

            //Campo 11
            $this->valorContabil = $valoresSegmento['valor_produtos'];


            // $valoresTotais[8] += $valorContabil - ($valorIPI + $valorST);
            // $valoresTotais[9] += $valores['vIPI']  * $percentual;
            // $valoresTotais[10] += $valores['vST']  * $percentual;
            // $valoresTotais[11] += $valorContabil;
        }


        // $valorBaseCalculo = (float) ($valoresSegmento['valor_base_calculo'] ?? 0);
        // $valorIcms = (float) ($valoresSegmento['valor_icms'] ?? 0);
        // $valorIcmsSt = (float) ($valoresSegmento['valor_icms_st'] ?? 0);

        // // Campo 4 - Base de cálculo
        // $this->baseCalculo = 0;

        // // Campo 6 - Valor do Imposto
        // $this->valorImposto = $valorIcms > 0 ? $valorIcms : 0;

        // //  Campo 7 - Valor de Isentas: quando não há valor de imposto, o valor contábil é considerado isento
        // //  Conforme exemplo do layout Domínio Sistemas
        // if ($this->valorImposto == 0 && $this->valorContabil > 0) {
        //     $this->valorIsentas = $this->valorContabil;
        //     // Zera a base de cálculo quando não há imposto
        //     $this->baseCalculo = 0;
        // } else {
        //     $this->valorIsentas = 0;
        // }

        // // Campo 8 - Valor de Outras: para operações com outros benefícios fiscais
        // $this->valorOutras = $valorBaseCalculo > 0 ? $valorBaseCalculo : 0;

        // // Campo 10 - Valor da substituição Tributária
        // $this->valorSubstituicaoTributaria = $valorIcmsSt > 0 ? $valorIcmsSt : 0;

        // // Campo 11 - Valor Contábil
        // $this->valorContabil = $valorBaseCalculo > 0 ? $valorBaseCalculo : 0;

        // // Campo 5 - Alíquota (calculada se houver base e valor)
        // // Trunca o valor para 2 casas decimais (não arredonda)
        // if ($this->baseCalculo > 0 && $this->valorImposto > 0) {
        //     $this->aliquota = floor(($this->valorImposto / $this->baseCalculo) * 10000) / 100;
        // }
    }

    /**
     * Preenche os campos para IPI (Código 2)
     * 
     * Regras contábeis:
     * - Base de cálculo: valor_produtos quando há IPI
     * - Valor do imposto: valor_ipi do segmento
     * - Valor de isentas: valor contábil quando não há IPI
     * - Valor do IPI: valor_ipi do segmento (campo específico)
     * 
     * @param array $valoresSegmento
     * @param NotaFiscalEletronica $notaFiscal
     * @return void
     */
    private function preencherIpi(array $valoresSegmento, NotaFiscalEletronica $notaFiscal): void
    {
        $valorIpi = (float) ($valoresSegmento['valor_ipi'] ?? 0);
        $valorProdutos = (float) ($valoresSegmento['valor_produtos'] ?? 0);

        // Campo 9 - Valor do IPI (campo específico do registro)
        $this->valorIpi = $valorIpi > 0 ? $valorIpi : 0;

        // Campo 4 - Base de cálculo: valor dos produtos quando há IPI
        if ($valorIpi > 0) {
            $this->baseCalculo = $valorProdutos;
            // Campo 6 - Valor do Imposto
            $this->valorImposto = $valorIpi;
        } else {
            // Quando não há IPI, o valor contábil é considerado isento
            $this->baseCalculo = 0;
            $this->valorImposto = 0;
            // Campo 7 - Valor de Isentas
            $this->valorIsentas = $this->valorContabil;
        }

        // Campo 8 - Valor de Outras: para operações com isenção parcial
        $this->valorOutras = 0;
    }

    /**
     * Preenche os campos para DIFAL (Código 8)
     * 
     * Regras contábeis:
     * - Base de cálculo: valor_base_calculo do segmento
     * - Valor do imposto: valor do DIFAL (vICMSUFDest)
     * - Alíquota interestadual: diferença entre alíquota interna e interestadual
     * 
     * @param array $valoresSegmento
     * @param NotaFiscalEletronica $notaFiscal
     * @return void
     */
    private function preencherDifal(array $valoresSegmento, NotaFiscalEletronica $notaFiscal): void
    {
        // Extrai o valor do DIFAL da nota fiscal
        $valorDifal = (float) ($notaFiscal->vICMSUFDest ?? 0);
        $valorBaseCalculo = (float) ($valoresSegmento['valor_base_calculo'] ?? 0);

        // Campo 4 - Base de cálculo
        $this->baseCalculo = $valorBaseCalculo > 0 ? $valorBaseCalculo : 0;

        // Campo 6 - Valor do Imposto (DIFAL)
        $this->valorImposto = $valorDifal > 0 ? $valorDifal : 0;

        // Campo 7 - Valor de Isentas: para DIFAL geralmente é zero
        $this->valorIsentas = 0;

        // Campo 8 - Valor de Outras: para DIFAL geralmente é zero
        $this->valorOutras = 0;

        // Campo 5 - Alíquota (calculada se houver base e valor)
        // Trunca o valor para 2 casas decimais (não arredonda)
        if ($this->baseCalculo > 0 && $this->valorImposto > 0) {
            $this->aliquota = floor(($this->valorImposto / $this->baseCalculo) * 10000) / 100;
        }

        // Campo 15 - Alíquota Interestadual para SP
        // Este campo é usado para informar a alíquota interestadual quando aplicável
        $this->aliquotaInterestadual = '';
    }

    /**
     * Retorna o tipo de registro
     * 
     * @return string
     */
    public function getTipoRegistro(): string
    {
        return '1020';
    }

    /**
     * Converte o registro para uma linha no formato TXT
     * 
     * @return string
     */
    public function converterParaLinhaTxt(): string
    {
        $campos = [
            $this->getTipoRegistro(), // Campo 1: Identificação do registro
            $this->formatarCampo($this->codigoImposto, null, 'N'), // Campo 2: Código do imposto
            $this->formatarCampo($this->percentualReducaoBaseCalculo, null, 'D2'), // Campo 3: Percentual de redução da base de cálculo
            $this->formatarCampo($this->baseCalculo, null, 'D2'), // Campo 4: Base de cálculo
            $this->formatarCampo($this->aliquota, null, 'D2'), // Campo 5: Alíquota
            $this->formatarCampo($this->valorImposto, null, 'D2'), // Campo 6: Valor do Imposto
            $this->formatarCampo($this->valorIsentas, null, 'D2'), // Campo 7: Valor de Isentas
            $this->formatarCampo($this->valorOutras, null, 'D2'), // Campo 8: Valor de Outras
            $this->formatarCampo($this->valorIpi, null, 'D2'), // Campo 9: Valor do IPI
            $this->formatarCampo($this->valorSubstituicaoTributaria, null, 'D2'), // Campo 10: Valor da substituição Tributária
            $this->formatarCampo($this->valorContabil, null, 'D2'), // Campo 11: Valor Contábil
            $this->formatarCampo($this->codigoRecolhimentoImposto, null, 'C'), // Campo 12: Código do recolhimento do imposto
            $this->formatarCampo($this->valorNaoTributadas, null, 'D2'), // Campo 13: Valor não tributadas (GO)
            $this->formatarCampo($this->valorParcelaReduzida, null, 'D2'), // Campo 14: Valor parcela reduzida (GO)
            $this->formatarCampo($this->aliquotaInterestadual, null, 'C'), // Campo 15: Alíq. Interest.
            $this->formatarCampo($this->naturezaRenda, null, 'C'), // Campo 16: Nat. rend.
            $this->formatarCampo($this->tipoDeducao, null, 'C'), // Campo 17: Tipo de Dedução
            $this->formatarCampo($this->tipoIsencao, null, 'C'), // Campo 18: Tipo de Isenção
            $this->formatarCampo($this->descricao, null, 'C'), // Campo 19: Descrição
        ];

        return $this->montarLinha($campos);
    }

    /**
     * Valida se o registro está em conformidade com o layout
     * 
     * @return bool
     */
    public function isValid(): bool
    {
        return $this->codigoImposto > 0 && $this->valorContabil >= 0;
    }

    /**
     * Retorna o código do imposto
     * 
     * @return int
     */
    public function getCodigoImposto(): int
    {
        return $this->codigoImposto;
    }

    /**
     * Retorna o valor contábil
     * 
     * @return float|null
     */
    public function getValorContabil(): ?float
    {
        return $this->valorContabil;
    }

    /**
     * Verifica se o ICMS deve ser zerado para a tag/issuer atual
     * Utiliza cache estático para evitar consultas repetidas ao banco de dados
     *
     * @return bool
     */
    private function isZeraIcms(): bool
    {
        // Gera a chave de cache única para esta combinação issuer/tag
        $cacheKey = "{$this->issuer->id}_{$this->tagged->code}";

        // Verifica se já está em cache
        if (isset(self::$zeraIcmsCache[$cacheKey])) {
            return self::$zeraIcmsCache[$cacheKey];
        }

        // Realiza a consulta no banco de dados
        $check = EntradasImpostosEquivalente::where('tag_id', $this->tagged->tag_id)
            ->where('issuer_id', $this->issuer->id)
            ->where('status_icms', true)
            ->first();

        // Armazena o resultado em cache
        $result = $check !== null;
        self::$zeraIcmsCache[$cacheKey] = $result;

        return $result;
    }

    /**
     * Verifica se o IPI deve ser zerado para a tag/issuer atual
     * Utiliza cache estático para evitar consultas repetidas ao banco de dados
     *
     * @return bool
     */
    private function isZeraIpi(): bool
    {
        // Gera a chave de cache única para esta combinação issuer/tag
        $cacheKey = "{$this->issuer->id}_{$this->tagged->code}";

        // Verifica se já está em cache
        if (isset(self::$zeraIpiCache[$cacheKey])) {
            return self::$zeraIpiCache[$cacheKey];
        }

        // Realiza a consulta no banco de dados
        $check = EntradasImpostosEquivalente::where('tag_id', $this->tagged->tag_id)
            ->where('issuer_id', $this->issuer->id)
            ->where('status_ipi', true)
            ->first();

        // Armazena o resultado em cache
        $result = $check !== null;
        self::$zeraIpiCache[$cacheKey] = $result;

        return $result;
    }


    public function tomaCreditoIcms(): bool
    {

        $isNfeTomaCreditoIcms = GeneralSetting::getValue(
            name: 'configuracoes_gerais',
            key: 'isNfeTomaCreditoIcms',
            default: false,
            issuerId: $this->issuer->id
        );

        if ($isNfeTomaCreditoIcms) {

            $tagsCreditoIcms = GeneralSetting::getValue(
                name: 'configuracoes_gerais',
                key: 'tagsCreditoIcms',
                default: false,
                issuerId: $this->issuer->id
            );

            if (in_array($this->tagged->tag_id, $tagsCreditoIcms)) {
                return true;
            }
        }

        return false;
    }
}
