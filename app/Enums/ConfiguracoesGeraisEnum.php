<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum ConfiguracoesGeraisEnum: string implements HasLabel
{
    case IsNfeClassificarNaEntrada = 'isNfeClassificarNaEntrada';
    case IsNfeManifestarAutomatica = 'isNfeManifestarAutomatica';
    case IsNfeClassificarSomenteManifestacao = 'isNfeClassificarSomenteManifestacao';
    case IsNfeMostrarCodigoEtiqueta = 'isNfeMostrarCodigoEtiqueta';
    case IsNfeTomaCreditoIcms = 'isNfeTomaCreditoIcms';
    case VerificarUfEmitenteDestinatario = 'verificar_uf_emitente_destinatario';
    case IsClassificarCteVinculadoANfe = 'isClassificarCteVinculadoANfe';

    public function getLabel(): string
    {
        return match ($this) {
            self::IsNfeClassificarNaEntrada => 'Data de Entrada na classificação da NFe',
            self::IsNfeManifestarAutomatica => 'Manifestação automática pelo Fiscaut',
            self::IsNfeClassificarSomenteManifestacao => 'Classificação somente após manifestação',
            self::IsNfeMostrarCodigoEtiqueta => 'Mostrar código da etiqueta ao invés do nome abreviado',
            self::IsClassificarCteVinculadoANfe => 'Classificar CTE vinculado a NFe quando etiquetada',
            self::IsNfeTomaCreditoIcms => 'Considerar como crédito de ICMS as NF com CFOP 1.401',
            self::VerificarUfEmitenteDestinatario => 'Verificar UF emitente X UF destinatário',
        };
    }

    public static function toArray()
    {
        $statuses = [];

        foreach (self::cases() as $status) {
            $statuses[$status->value] = $status->getLabel();
        }

        return $statuses;
    }
}
