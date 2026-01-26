<?php

namespace App\Services\Xml;

use Exception;
use SimpleXMLElement;

class XmlIdentifierService
{
    // Tipos de documentos
    public const TIPO_NFE = 'NFE';

    public const TIPO_NFE_RESUMO = 'NFE_RESUMO';

    public const TIPO_CTE = 'CTE';

    public const TIPO_NFSE = 'NFSE';

    // Tipos de eventos específicos
    public const TIPO_EVENTO_NFE = 'EVENTO_NFE';

    public const TIPO_EVENTO_CTE = 'EVENTO_CTE';

    public const TIPO_EVENTO = 'EVENTO'; // Mantido para compatibilidade

    /**
     * Identifica o tipo de documento XML (NFe, CTe, NFSe) ou evento
     *
     * @param  string  $xmlContent  Conteúdo do XML a ser identificado
     * @return string Tipo do documento ou evento identificado
     *
     * @throws Exception Quando não for possível identificar o tipo do XML
     */
    public static function identificarTipoXml(string $xmlContent): string
    {
        try {
            $xml = new SimpleXMLElement($xmlContent);
            $rootName = $xml->getName();

            // Identificação por nome do elemento raiz
            switch ($rootName) {
                case 'nfeProc':
                case 'NFe':
                    return self::TIPO_NFE;

                case 'resNFe':
                    return self::TIPO_NFE_RESUMO;

                case 'cteProc':
                case 'CTe':
                    return self::TIPO_CTE;

                case 'procEventoNFe':
                case 'resEvento':
                    // Para resEvento, verificar se contém chNFe ou chCTe
                    if (isset($xml->chNFe) || (isset($xml->infEvento) && isset($xml->infEvento->chNFe))) {
                        return self::TIPO_EVENTO_NFE;
                    } elseif (isset($xml->chCTe) || (isset($xml->infEvento) && isset($xml->infEvento->chCTe))) {
                        return self::TIPO_EVENTO_CTE;
                    }

                    return self::TIPO_EVENTO_NFE; // Default para NFe por compatibilidade

                case 'procEventoCTe':
                    return self::TIPO_EVENTO_CTE;

                case 'evento':
                    // Verificar se é evento de NFe ou CTe baseado na chave
                    if (isset($xml->infEvento)) {
                        if (isset($xml->infEvento->chNFe)) {
                            return self::TIPO_EVENTO_NFE;
                        } elseif (isset($xml->infEvento->chCTe)) {
                            return self::TIPO_EVENTO_CTE;
                        }
                    }

                    return self::TIPO_EVENTO_NFE; // Default para NFe por compatibilidade

                case 'eventoCTe':
                    return self::TIPO_EVENTO_CTE;

                default:
                    // Verificação adicional para NFSe (múltiplos padrões)
                    if (isset($xml->InfNfse) || isset($xml->Nfse) || isset($xml->CompNfse) ||
                        isset($xml->GerarNfseResposta) || isset($xml->ConsultarNfseResposta) ||
                        isset($xml->ConsultarLoteRpsResposta)) {
                        return self::TIPO_NFSE;
                    }

                    // Verificação adicional para NFe dentro de outros elementos
                    if (isset($xml->NFe)) {
                        return self::TIPO_NFE;
                    }

                    // Verificação adicional para CTe dentro de outros elementos
                    if (isset($xml->CTe)) {
                        return self::TIPO_CTE;
                    }

                    throw new Exception('XML não identificado como NFe, CTe, NFSe ou evento');
            }
        } catch (Exception $e) {
            throw new Exception('Erro ao identificar tipo do XML: '.$e->getMessage());
        }
    }

    /**
     * Obtém informações básicas do evento (simplificado)
     *
     * @param  string  $xmlContent  Conteúdo do XML do evento
     * @return array Informações básicas do evento
     */
    public static function obterDetalhesEvento(string $xmlContent): array
    {
        $xml = new SimpleXMLElement($xmlContent);

        // Determina o nó que contém as informações do evento
        $evento = null;
        $infEvento = null;

        if (isset($xml->evento)) {
            $evento = $xml->evento;
            $infEvento = $evento->infEvento;
        } elseif ($xml->getName() === 'evento') {
            $evento = $xml;
            $infEvento = $evento->infEvento;
        } elseif (isset($xml->eventoCTe)) {
            $evento = $xml->eventoCTe;
            $infEvento = $evento->infEvento;
        } elseif ($xml->getName() === 'eventoCTe') {
            $evento = $xml;
            $infEvento = $evento->infEvento;
        } elseif (isset($xml->procEventoNFe)) {
            $evento = $xml->procEventoNFe->evento;
            $infEvento = $evento->infEvento;
        } elseif (isset($xml->procEventoCTe)) {
            $evento = $xml->procEventoCTe->eventoCTe;
            $infEvento = $evento->infEvento;
        } elseif (isset($xml->resEvento)) {
            $evento = $xml->resEvento;
            $infEvento = $evento;
        } elseif ($xml->getName() === 'resEvento') {
            $evento = $xml;
            $infEvento = $evento;
        }

        if ($evento === null) {
            throw new Exception('Estrutura de evento não reconhecida');
        }

        // Determinar o tipo específico do evento
        $tipoEvento = self::TIPO_EVENTO;
        $chave = '';

        if (isset($infEvento->chNFe)) {
            $tipoEvento = self::TIPO_EVENTO_NFE;
            $chave = (string) $infEvento->chNFe;
        } elseif (isset($infEvento->chCTe)) {
            $tipoEvento = self::TIPO_EVENTO_CTE;
            $chave = (string) $infEvento->chCTe;
        }

        // Informações básicas do evento
        $detalhes = [
            'tipo' => $tipoEvento,
            'chave' => $chave,
            'tpEvento' => (string) $infEvento->tpEvento,
            'nSeqEvento' => (string) ($infEvento->nSeqEvento ?? ''),
            'dhEvento' => (string) $infEvento->dhEvento,
            'id' => (string) ($infEvento['Id'] ?? ''),
        ];

        // Para resEvento, adicionar informações específicas
        if (isset($xml->resEvento) || $xml->getName() === 'resEvento') {
            $detalhes['dhRecbto'] = (string) ($infEvento->dhRecbto ?? '');
            $detalhes['nProt'] = (string) ($infEvento->nProt ?? '');
            $detalhes['xEvento'] = (string) ($infEvento->xEvento ?? '');
        }

        return $detalhes;
    }
}
