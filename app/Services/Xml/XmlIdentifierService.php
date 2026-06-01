<?php

namespace App\Services\Xml;

use Exception;
use SimpleXMLElement;

class XmlIdentifierService
{
    // Tipos de documentos
    public const TIPO_NFE = 'NFE';

    public const TIPO_NFE_RESUMO = 'NFE_RESUMO';

    public const TIPO_NFCE = 'NFCE';

    public const TIPO_CTE = 'CTE';

    public const TIPO_NFSE = 'NFSE';

    // Tipos de eventos específicos
    public const TIPO_EVENTO_NFE = 'EVENTO_NFE';

    public const TIPO_EVENTO_CTE = 'EVENTO_CTE';

    public const TIPO_EVENTO_NFSE = 'EVENTO_NFSE';

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
                    return self::identificarModeloNFe($xml);

                case 'resNFe':
                    $modelo = self::identificarModeloNFe($xml);

                    return $modelo === self::TIPO_NFCE ? self::TIPO_NFCE : self::TIPO_NFE_RESUMO;

                case 'resNFCe':
                    return self::TIPO_NFCE;

                case 'cteProc':
                case 'CTe':
                    return self::TIPO_CTE;

                case 'procEventoNFe':
                case 'resEvento':
                    // Para resEvento, verificar se contém chNFe ou chCTe ou chNFSe
                    if (isset($xml->chNFSe) || (isset($xml->infEvento) && isset($xml->infEvento->chNFSe))) {
                        return self::TIPO_EVENTO_NFSE;
                    } elseif (isset($xml->chNFe) || (isset($xml->infEvento) && isset($xml->infEvento->chNFe))) {
                        return self::TIPO_EVENTO_NFE;
                    } elseif (isset($xml->chCTe) || (isset($xml->infEvento) && isset($xml->infEvento->chCTe))) {
                        return self::TIPO_EVENTO_CTE;
                    }

                    return self::TIPO_EVENTO_NFE; // Default para NFe por compatibilidade

                case 'procEventoCTe':
                    return self::TIPO_EVENTO_CTE;

                case 'evento':
                    // Verificar se é evento de NFe, CTe ou NFSe baseado na chave
                    if (isset($xml->infEvento)) {
                        if (isset($xml->infEvento->chNFe)) {
                            return self::TIPO_EVENTO_NFE;
                        } elseif (isset($xml->infEvento->chCTe)) {
                            return self::TIPO_EVENTO_CTE;
                        }

                        // Evento NFSe pode ter chNFSe diretamente ou via pedRegEvento (formato SIEG)
                        if (isset($xml->infEvento->chNFSe) || isset($xml->infEvento->pedRegEvento)) {
                            return self::TIPO_EVENTO_NFSE;
                        }

                        // Verificar com namespace (XML de evento NFSe usa xmlns)
                        $namespaces = $xml->getNamespaces(true);
                        if (! empty($namespaces)) {
                            $ns = $namespaces[''] ?? null;
                            if ($ns) {
                                $infEvento = $xml->children($ns)->infEvento;
                                if (isset($infEvento->chNFSe) || isset($infEvento->pedRegEvento)) {
                                    return self::TIPO_EVENTO_NFSE;
                                }
                            }
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

                    // Verificação adicional para NFe/NFCe dentro de outros elementos
                    if (isset($xml->NFe)) {
                        return self::identificarModeloNFe($xml);
                    }

                    // Verificação adicional para CTe dentro de outros elementos
                    if (isset($xml->CTe)) {
                        return self::TIPO_CTE;
                    }

                    throw new Exception('XML não identificado como NFe, NFCe, CTe, NFSe ou evento');
            }
        } catch (Exception $e) {
            throw new Exception('Erro ao identificar tipo do XML: '.$e->getMessage());
        }
    }

    /**
     * Identifica se é NFe ou NFCe baseado no modelo (55 ou 65)
     */
    private static function identificarModeloNFe(SimpleXMLElement $xml): string
    {
        // Primeiro tenta obter de NFe/infNFe/ide/mod
        $nfe = $xml->NFe ?? null;
        if ($nfe !== null) {
            $infNFe = $nfe->infNFe ?? null;
        } else {
            $infNFe = $xml->infNFe ?? null;
        }

        if ($infNFe !== null && isset($infNFe->ide->mod)) {
            $mod = (string) $infNFe->ide->mod;
            if ($mod === '65') {
                return self::TIPO_NFCE;
            }
        }

        // Para resNFe, verificar a chave (modelo está na posição 22-23 da chave)
        $chNFe = $xml->chNFe ?? null;
        if ($chNFe !== null) {
            $chave = (string) $chNFe;
            if (strlen($chave) >= 23) {
                $mod = substr($chave, 21, 2);
                if ($mod === '65') {
                    return self::TIPO_NFCE;
                }
            }
        }

        return self::TIPO_NFE;
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
