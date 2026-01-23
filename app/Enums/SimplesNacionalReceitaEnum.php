<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * Enum com os tipos de receitas do Simples Nacional, gerado a partir de $camposReceita.
 */
enum SimplesNacionalReceitaEnum: string implements HasLabel
{
    case ATIVIDADE_01_REVENDA_INTERNA_SEM_ST = 'atividade_01_revenda_interna_sem_st';
    case ATIVIDADE_01_REVENDA_INTERNA_COM_ST = 'atividade_01_revenda_interna_com_st';
    case ATIVIDADE_02_REVENDA_EXTERNA = 'atividade_02_revenda_externa';
    // case ATIVIDADE_03_INDUSTRIALIZADAS_INTERNA_SEM_ST = 'atividade_03_industrializadas_interna_sem_st';
    // case ATIVIDADE_03_INDUSTRIALIZADAS_INTERNA_COM_ST = 'atividade_03_industrializadas_interna_com_st';
    // case ATIVIDADE_04_INDUSTRIALIZADAS_EXTERNA = 'atividade_04_industrializadas_externa';
    // case ATIVIDADE_05_LOCACAO_INTERNA = 'atividade_05_locacao_interna';
    // case ATIVIDADE_06_LOCACAO_EXTERNA = 'atividade_06_locacao_externa';
    // case ATIVIDADE_07_SERVICOS_FATOR_R_ANEXO3 = 'atividade_07_servicos_fator_r_anexo3';
    // case ATIVIDADE_08_SERVICOS_SEM_FATOR_R_ANEXO3 = 'atividade_08_servicos_sem_fator_r_anexo3';
    // case ATIVIDADE_09_SERVICOS_ANEXO4 = 'atividade_09_servicos_anexo4';
    // case ATIVIDADE_10_SERVICOS_RETENCAO_ISS = 'atividade_10_servicos_retencao_iss';
    // case ATIVIDADE_11_SERVICOS_SEM_RETENCAO_ISS = 'atividade_11_servicos_sem_retencao_iss';
    // case ATIVIDADE_12_SERVICOS_EXTERIOR = 'atividade_12_servicos_exterior';
    // case ATIVIDADE_13_IPI_SIMULTANEO = 'atividade_13_ipi_simultaneo';
    // case ATIVIDADE_14_ISS_SIMULTANEO = 'atividade_14_iss_simultaneo';

    /**
     * Retorna todas as chaves em array de string.
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Retorna um rótulo amigável para exibição.
     */
    public function getLabel(): ?string
    {
        return match ($this) {
            self::ATIVIDADE_01_REVENDA_INTERNA_SEM_ST => 'Revenda interna (sem ST)',
            self::ATIVIDADE_01_REVENDA_INTERNA_COM_ST => 'Revenda interna (com ST)',
            self::ATIVIDADE_02_REVENDA_EXTERNA => 'Revenda externa',
            // self::ATIVIDADE_03_INDUSTRIALIZADAS_INTERNA_SEM_ST => 'Industrializadas interna (sem ST)',
            // self::ATIVIDADE_03_INDUSTRIALIZADAS_INTERNA_COM_ST => 'Industrializadas interna (com ST)',
            // self::ATIVIDADE_04_INDUSTRIALIZADAS_EXTERNA => 'Industrializadas externa',
            // self::ATIVIDADE_05_LOCACAO_INTERNA => 'Locação interna',
            // self::ATIVIDADE_06_LOCACAO_EXTERNA => 'Locação externa',
            // self::ATIVIDADE_07_SERVICOS_FATOR_R_ANEXO3 => 'Serviços fator R (Anexo III)',
            // self::ATIVIDADE_08_SERVICOS_SEM_FATOR_R_ANEXO3 => 'Serviços sem fator R (Anexo III)',
            // self::ATIVIDADE_09_SERVICOS_ANEXO4 => 'Serviços (Anexo IV)',
            // self::ATIVIDADE_10_SERVICOS_RETENCAO_ISS => 'Serviços com retenção de ISS',
            // self::ATIVIDADE_11_SERVICOS_SEM_RETENCAO_ISS => 'Serviços sem retenção de ISS',
            // self::ATIVIDADE_12_SERVICOS_EXTERIOR => 'Serviços ao exterior',
            // self::ATIVIDADE_13_IPI_SIMULTANEO => 'IPI simultâneo',
            // self::ATIVIDADE_14_ISS_SIMULTANEO => 'ISS simultâneo',
        };
    }
}
