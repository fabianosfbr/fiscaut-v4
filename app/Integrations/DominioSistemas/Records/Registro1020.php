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
     * Regras contábeis aplicadas:
     * - CST 00: Tributado integralmente. Base e Imposto preenchidos.
     * - CST 10: Tributado com ST. Base e Imposto preenchidos. ST ignorado neste registro (foco no ICMS próprio) ou tratado se houver campo.
     * - CST 20: Redução de base. Diferença vai para Isentas/Outras.
     * - CST 30/40/41/50/60: Isenções/Não incidência. Valor vai para Isentas/Outras.
     * - CST 51: Diferimento. Pode ter valor parcial.
     * - CST 90: Outros.
     * 
     * O sistema verifica configurações de "Zerar ICMS" (EntradasImpostosEquivalente).
     * Se "Zerar ICMS" estiver ativo, Base e Imposto são zerados e o valor vai para Isentas/Outras.
     */
    private function preencherIcms(array $valoresSegmento, NotaFiscalEletronica $notaFiscal): void
    {
        $cst = (string) ($valoresSegmento['valor_cst'] ?? '');
        $csosn = (string) ($valoresSegmento['csosn'] ?? $valoresSegmento['CSOSN'] ?? '');

        // Se CST vier vazio e tiver CSOSN, converte para CST equivalente para processamento
        if (empty($cst) && !empty($csosn)) {
            $cst = match ($csosn) {
                '101' => '00', // Permite crédito
                '102', '103', '300', '400' => '41', // Não permite crédito (Não tributada)
                '201' => '10', // Tributada com ST e com crédito
                '202', '203' => '30', // Tributada com ST sem crédito (Isenta ou não tributada)
                '500' => '60', // Cobrado anteriormente por substituição tributária
                '900' => '90', // Outros
                default => '90'
            };
        }

        $valorProdutos = (float) ($valoresSegmento['valor_produtos'] ?? 0);
        $valorBaseCalculo = (float) ($valoresSegmento['valor_base_calculo'] ?? 0);
        $percentualIcms = (float) ($valoresSegmento['percentual_icms'] ?? 0);
        $valorIcms = (float) ($valoresSegmento['valor_icms'] ?? 0);
        $valorIpi = (float) ($valoresSegmento['valor_ipi'] ?? 0);
        // $valorIcmsSt = (float) ($valoresSegmento['valor_icms_st'] ?? 0); // Se necessário para lógica futura

        // Valores padrão
        $this->valorContabil = $valorProdutos;
        $this->valorIpi = $valorIpi;
        $this->valorSubstituicaoTributaria = 0; // Padrão zero, ajustar se necessário
        
        // Verifica se deve zerar ICMS (regra de negócio/configuração)
        if ($this->isZeraIcms($this->issuer, $this->tagged->tag_id)) {
            $this->baseCalculo = 0;
            $this->aliquota = 0;
            $this->valorImposto = 0;
            $this->valorIsentas = 0;
            $this->valorOutras = $this->valorContabil; // Move tudo para Outras
            return;
        }

        // Lógica por CST
        switch ($cst) {
            case '00': // Tributada integralmente
                $this->baseCalculo = $valorBaseCalculo;
                $this->aliquota = $percentualIcms;
                $this->valorImposto = $valorIcms;
                $this->valorIsentas = 0;
                $this->valorOutras = 0;
                break;

            case '10': // Tributada e com cobrança de ICMS por substituição tributária
                // Aqui foca-se no ICMS próprio. O ST geralmente vai em outro campo ou registro, 
                // mas o layout tem campo 10 (ST). O código original zerava. Manteremos foco no próprio.
                $this->baseCalculo = $valorBaseCalculo;
                $this->aliquota = $percentualIcms;
                $this->valorImposto = $valorIcms;
                $this->valorIsentas = 0;
                $this->valorOutras = 0; 
                break;

            case '20': // Com redução de base de cálculo
            case '70': // Com redução de base de cálculo e cobrança de ICMS por ST
                $this->baseCalculo = $valorBaseCalculo;
                $this->aliquota = $percentualIcms;
                $this->valorImposto = $valorIcms;
                $this->valorIsentas = $this->valorContabil - $valorBaseCalculo; // Diferença é isenta
                if ($this->valorIsentas < 0) $this->valorIsentas = 0;
                $this->valorOutras = 0;
                break;

            case '30': // Isenta ou não tributada e com cobrança de ICMS por ST
            case '40': // Isenta
            case '41': // Não tributada
            case '50': // Suspensão
                // Nestes casos, não há débito de ICMS próprio
                $this->baseCalculo = 0;
                $this->aliquota = 0;
                $this->valorImposto = 0;
                $this->valorIsentas = $this->valorContabil; // Tudo Isento
                $this->valorOutras = 0;
                break;
            
            case '60': // ICMS cobrado anteriormente por ST
                // ST recolhido anteriormente -> Coluna Outras
                $this->baseCalculo = 0;
                $this->aliquota = 0;
                $this->valorImposto = 0;
                $this->valorIsentas = 0;
                $this->valorOutras = $this->valorContabil;
                break;

            case '51': // Diferimento
                // Diferimento pode ser total ou parcial.
                // Se parcial, tem base e imposto. Se total, comporta-se como isento/outras.
                // Assumindo valores vindos do XML/Segmento:
                $this->baseCalculo = $valorBaseCalculo;
                $this->aliquota = $percentualIcms;
                $this->valorImposto = $valorIcms;
                
                $diferenca = $this->valorContabil - $valorBaseCalculo;
                $this->valorIsentas = 0;
                $this->valorOutras = $diferenca > 0 ? $diferenca : 0; // Diferimento vai para Outras
                break;
            
            case '90': // Outras
                $this->baseCalculo = $valorBaseCalculo;
                $this->aliquota = $percentualIcms;
                $this->valorImposto = $valorIcms;
                
                $diferenca = $this->valorContabil - $valorBaseCalculo;
                $this->valorIsentas = 0;
                $this->valorOutras = $diferenca > 0 ? $diferenca : 0;
                break;

            default: // CST desconhecido ou não mapeado (70, etc.)
                // Tratamento genérico: usa o que vier
                $this->baseCalculo = $valorBaseCalculo;
                $this->aliquota = $percentualIcms;
                $this->valorImposto = $valorIcms;
                $this->valorIsentas = 0;
                $this->valorOutras = ($this->valorContabil - $valorBaseCalculo) > 0 ? ($this->valorContabil - $valorBaseCalculo) : 0;
                break;
        }
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
        $cstIpi = (string) ($valoresSegmento['valor_cst_ipi'] ?? $valoresSegmento['cst_ipi'] ?? '');
        $valorIpi = (float) ($valoresSegmento['valor_ipi'] ?? 0);
        $valorProdutos = (float) ($valoresSegmento['valor_produtos'] ?? 0);

      
        // Verifica se deve zerar IPI (regra de negócio/configuração)
        if ($this->isZeraIpi($this->issuer, $this->tagged->tag_id)) {
            $this->valorIpi = 0;
            $this->baseCalculo = 0;
            $this->valorOutras = $this->valorContabil;
            $this->valorContabil = $this->valorContabil;
            $this->valorImposto = 0;
            $this->valorIsentas = 0;
            
            return;
        }

        // Campo 9 - Valor do IPI (campo específico do registro)
        $this->valorIpi = $valorIpi > 0 ? $valorIpi : 0;
       
        // Se tiver CST, usa lógica específica
        if ($cstIpi !== '') {
            switch ($cstIpi) {
                case '00': // Entrada com recuperação de crédito
                case '50': // Saída tributada (eventual)
                    $this->baseCalculo = $valorProdutos;
                    $this->valorImposto = $valorIpi;
                    $this->valorIsentas = 0;
                    $this->valorOutras = 0;
                    break;

                case '49': // Outras entradas
                case '99': // Outras saídas
                    if ($valorIpi > 0) {
                        $this->baseCalculo = $valorProdutos;
                        $this->valorImposto = $valorIpi;
                        $this->valorIsentas = 0;
                        $this->valorOutras = 0;
                    } else {
                        $this->baseCalculo = 0;
                        $this->valorImposto = 0;
                        $this->valorIsentas = 0;
                        $this->valorOutras = $this->valorContabil;
                    }
                    break;

                case '01': // Entrada tributada com alíquota zero
                case '02': // Entrada isenta
                case '03': // Entrada não-tributada
                case '04': // Entrada imune
                case '05': // Entrada com suspensão
                case '51': // Saída tributada com alíquota zero
                case '52': // Saída isenta
                case '53': // Saída não-tributada
                case '54': // Saída imune
                case '55': // Saída com suspensão
                    $this->baseCalculo = 0;
                    $this->valorImposto = 0;
                    $this->valorIsentas = $this->valorContabil;
                    $this->valorOutras = 0;
                    break;
                
                default:
                    // Fallback para lógica baseada em valor se CST não for reconhecido
                    if ($valorIpi > 0) {
                        $this->baseCalculo = $valorProdutos;
                        $this->valorImposto = $valorIpi;
                        $this->valorIsentas = 0;
                    } else {
                        $this->baseCalculo = 0;
                        $this->valorImposto = 0;
                        $this->valorIsentas = $this->valorContabil;
                    }
                    $this->valorOutras = 0;
                    break;
            }
        } else {
            // Lógica legada (sem CST)
            if ($valorIpi > 0) {
                $this->baseCalculo = $valorProdutos;
                // Campo 6 - Valor do Imposto
                $this->valorImposto = $valorIpi;
                // Campo 7 - Valor de Isentas
                $this->valorIsentas = 0;
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
        $this->baseCalculo =  $valorDifal > 0 ? $valorBaseCalculo : 0;

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

        $this->valorContabil = $valorDifal > 0 ? $valorDifal : 0;

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
                default: [],
                issuerId: $this->issuer->id
            );

            if (is_array($tagsCreditoIcms) && in_array($this->tagged->tag_id, $tagsCreditoIcms)) {
                return true;
            }
        }

        return false;
    }
}
