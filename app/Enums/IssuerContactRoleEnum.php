<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum IssuerContactRoleEnum: string implements HasLabel
{
    case SINDICO = 'sindico';
    case SUB_SINDICO = 'sub_sindico';
    case CONSELHEIRO = 'conselheiro';
    case ADMINISTRADOR = 'administrador';
    case DEMAIS = 'demais';
    case PRESIDENTE = 'presidente';
    case VICE_PRESIDENTE = 'vice_presidente';
    case SECRETARIO = 'secretario';
    case DIRETORIA = 'diretoria';
    case CONSELHO = 'conselho';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::SINDICO => 'Síndico',
            self::SUB_SINDICO => 'Sub-Síndico',
            self::CONSELHEIRO => 'Conselheiro',
            self::ADMINISTRADOR => 'Administrador',
            self::DEMAIS => 'Demais',
            self::PRESIDENTE => 'Presidente',
            self::VICE_PRESIDENTE => 'Vice-Presidente',
            self::SECRETARIO => 'Secretário',
            self::DIRETORIA => 'Diretoria',
            self::CONSELHO => 'Conselho',
        };
    }

    public static function getOptions(IssuerTypeEnum $issuerType): array
    {
        return match ($issuerType) {
            IssuerTypeEnum::CONDOMINIO => [
                self::SINDICO->value => self::SINDICO->getLabel(),
                self::SUB_SINDICO->value => self::SUB_SINDICO->getLabel(),
                self::CONSELHEIRO->value => self::CONSELHEIRO->getLabel(),
                self::ADMINISTRADOR->value => self::ADMINISTRADOR->getLabel(),
                self::DEMAIS->value => self::DEMAIS->getLabel(),
            ],
            IssuerTypeEnum::ASSOCIACAO => [
                self::PRESIDENTE->value => self::PRESIDENTE->getLabel(),
                self::VICE_PRESIDENTE->value => self::VICE_PRESIDENTE->getLabel(),
                self::SECRETARIO->value => self::SECRETARIO->getLabel(),
                self::DIRETORIA->value => self::DIRETORIA->getLabel(),
                self::CONSELHO->value => self::CONSELHO->getLabel(),
                self::ADMINISTRADOR->value => self::ADMINISTRADOR->getLabel(),
                self::DEMAIS->value => self::DEMAIS->getLabel(),
            ],
            default => [],
        };
    }
}
