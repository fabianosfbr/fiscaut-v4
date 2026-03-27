<?php

namespace App\Filament\Condominio\Resources\IssuerAssembleias\Widgets;

use App\Enums\AssembleiaStatusEnum;
use App\Enums\IssuerAssembleiaPrazoTecnicoEnum;
use App\Models\IssuerAssembleia;
use Filament\Widgets\Widget;

class IssuerAssembleiaPrazoTecnicoOverview extends Widget
{
    protected string $view = 'filament.condominio.widgets.issuer-assembleia-prazo-tecnico-overview';

    //protected int | string | array $columnSpan = 'full';

    protected function getViewData(): array
    {
        $issuer = currentIssuer();

        $counts = collect(IssuerAssembleiaPrazoTecnicoEnum::cases())
            ->mapWithKeys(fn(IssuerAssembleiaPrazoTecnicoEnum $status) => [$status->value => 0])
            ->all();

        $emAndamentoCount = 0;

        if ($issuer) {
            $assembleias = IssuerAssembleia::query()
                ->where('issuer_id', $issuer->id)
                ->get();

            foreach ($assembleias as $assembleia) {
                if (
                    $assembleia->assembleia_status instanceof AssembleiaStatusEnum
                    && $assembleia->assembleia_status !== AssembleiaStatusEnum::DRAFT
                ) {
                    $emAndamentoCount++;
                }

                $status = $assembleia->prazoTecnicoStatus();

                if (! $status) {
                    continue;
                }

                $counts[$status->value] = ($counts[$status->value] ?? 0) + 1;
            }
        }

        $items = [
            [
                'label' => 'Em andamento',
                'count' => $emAndamentoCount,
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
                'label' => 'Atrasadas',
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
