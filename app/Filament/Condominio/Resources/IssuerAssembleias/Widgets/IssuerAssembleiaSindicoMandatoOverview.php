<?php

namespace App\Filament\Condominio\Resources\IssuerAssembleias\Widgets;

use App\Enums\AssembleiaStatusEnum;
use App\Enums\IssuerAssembleiaPrazoTecnicoEnum;
use App\Models\IssuerAssembleia;
use Filament\Widgets\Widget;

class IssuerAssembleiaSindicoMandatoOverview extends Widget
{
    protected string $view = 'filament.condominio.widgets.issuer-assembleia-sindico-mandato-overview';

    protected int|string|array $columnSpan = 'full';

    protected function getViewData(): array
    {
        $issuer = currentIssuer();

        $counts = collect(IssuerAssembleiaPrazoTecnicoEnum::cases())
            ->mapWithKeys(fn (IssuerAssembleiaPrazoTecnicoEnum $status) => [$status->value => 0])
            ->all();

        $comMandatoCount = 0;

        if ($issuer) {
            $assembleias = IssuerAssembleia::query()
                ->where('issuer_id', $issuer->id)
                ->get();

            foreach ($assembleias as $assembleia) {
                if (
                    $assembleia->assembleia_status instanceof AssembleiaStatusEnum
                    && $assembleia->assembleia_status !== AssembleiaStatusEnum::DRAFT
                    && $assembleia->mandato_fim
                ) {
                    $comMandatoCount++;
                }

                $status = $assembleia->sindicoMandatoStatus();

                if (! $status) {
                    continue;
                }

                $counts[$status->value] = ($counts[$status->value] ?? 0) + 1;
            }
        }

        $items = [
            [
                'label' => 'Com mandato definido',
                'count' => $comMandatoCount,
                'color' => '#0d6efd',
            ],
            [
                'label' => 'Antes do prazo',
                'status' => IssuerAssembleiaPrazoTecnicoEnum::ANTES_DO_PRAZO,
            ],
            [
                'label' => '1º Prazo técnico',
                'status' => IssuerAssembleiaPrazoTecnicoEnum::PRIMEIRO,
            ],
            [
                'label' => '2º Prazo técnico',
                'status' => IssuerAssembleiaPrazoTecnicoEnum::SEGUNDO,
            ],
            [
                'label' => '3º Prazo técnico',
                'status' => IssuerAssembleiaPrazoTecnicoEnum::TERCEIRO,
            ],
            [
                'label' => '4º Prazo técnico',
                'status' => IssuerAssembleiaPrazoTecnicoEnum::QUARTO,
            ],
            [
                'label' => 'Mandato expirado',
                'status' => IssuerAssembleiaPrazoTecnicoEnum::ATRASADO,
            ],
        ];

        return [
            'items' => array_map(function (array $item) use ($counts) {
                if (array_key_exists('count', $item)) {
                    return $item;
                }

                /** @var IssuerAssembleiaPrazoTecnicoEnum $status */
                $status = $item['status'];

                return [
                    'label' => $item['label'],
                    'count' => $counts[$status->value] ?? 0,
                    'color' => $status->getColorHex(),
                ];
            }, $items),
        ];
    }
}
