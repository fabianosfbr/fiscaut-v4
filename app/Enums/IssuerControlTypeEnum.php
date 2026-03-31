<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum IssuerControlTypeEnum: string implements HasLabel
{
    case PREVENTIVA = 'preventiva';
    case CORRETIVA = 'corretiva';
    case INSPECAO = 'inspecao';
    case CALIBRACAO = 'calibracao';
    case ADMINISTRATIVA = 'administrativa';
    case DOCUMENTAL = 'documental';

    public function getLabel(): string
    {
        return match ($this) {
            self::PREVENTIVA => 'Preventiva',
            self::CORRETIVA => 'Corretiva',
            self::INSPECAO => 'Inspeção',
            self::CALIBRACAO => 'Calibração',
            self::ADMINISTRATIVA => 'Administrativa',
            self::DOCUMENTAL => 'Documental',
        };
    }

    public static function toArray(): array
    {
        $categories = [];

        foreach (self::cases() as $category) {
            $categories[$category->value] = $category->getLabel();
        }

        return $categories;
    }

    public function getColor(): string
    {
        return match ($this) {
            self::PREVENTIVA => 'success',
            self::CORRETIVA => 'warning',
            self::INSPECAO => 'info',
            self::CALIBRACAO => 'primary',
            self::ADMINISTRATIVA => 'secondary',
            self::DOCUMENTAL => 'info',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::PREVENTIVA => 'heroicon-o-shield-check',
            self::CORRETIVA => 'heroicon-o-wrench-screwdriver',
            self::INSPECAO => 'heroicon-o-magnifying-glass',
            self::CALIBRACAO => 'heroicon-o-adjustments-horizontal',
            self::ADMINISTRATIVA => 'heroicon-o-document-text',
            self::DOCUMENTAL => 'heroicon-o-clipboard-document',
        };
    }

    public function getDescription(): string
    {
        return match ($this) {
            self::PREVENTIVA => 'Manutenção realizada para prevenir falhas e prolongar a vida útil',
            self::CORRETIVA => 'Manutenção realizada para corrigir falhas ou problemas identificados',
            self::INSPECAO => 'Verificação e análise do estado de equipamentos e instalações',
            self::CALIBRACAO => 'Ajuste e verificação da precisão de instrumentos e equipamentos',
            self::ADMINISTRATIVA => 'Controle de documentos, licenças e obrigações administrativas',
            self::DOCUMENTAL => 'Gestão de documentos, certidões e comprovantes',
        };
    }
}
