<?php

namespace App\Filament\Resources\Issuers\Tables;

use App\Filament\Resources\Issuers\Actions\DownloadCertificadoAction;
use App\Filament\Resources\Issuers\Actions\GerenciarServicoAction;
use App\Filament\Resources\Issuers\Actions\RemoveCertificadoAction;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;

class IssuersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordUrl(null)
            ->modifyQueryUsing(function (Builder $query): Builder {

                $user = Auth::user();

                if ($user && $user->tenant_id) {
                    $query->where('tenant_id', $user->tenant_id);
                } else {
                    // Se não há usuário autenticado ou issuer, retorna query vazia
                    $query->whereRaw('1 = 0');
                }

                return $query;
            })
            ->defaultSort('razao_social', 'asc')
            ->columns(self::getColumns())
            ->filters(self::getFilters())
            ->recordActions(self::getRecordActions())
            ->toolbarActions([]);
    }

    protected static function getColumns(): array
    {
        return [
            TextColumn::make('razao_social')
                ->label('Razão Social')
                ->sortable()
                ->searchable()
                ->getStateUsing(function (Model $record) {
                    $issuer = explode(':', $record->razao_social);

                    return $issuer[0];
                })
                ->limit(30)
                ->tooltip(function (TextColumn $column): ?string {
                    $state = $column->getState();
                    if (strlen($state) <= 30) {
                        return null;
                    }

                    return $state;
                })
                ->weight('medium'),

            TextColumn::make('cnpj')
                ->label('CNPJ')
                ->sortable()
                ->searchable()
                ->copyable()
                ->copyMessage('CNPJ copiado!')
                ->copyMessageDuration(1500),

            IconColumn::make('is_enabled')
                ->label('Status')
                ->boolean()
                ->trueIcon('heroicon-o-check-circle')
                ->falseIcon('heroicon-o-x-circle')
                ->trueColor('success')
                ->falseColor('danger')
                ->sortable(),

            TextColumn::make('validade_certificado')
                ->label('Certificado')
                ->sortable()
                ->formatStateUsing(function ($state, Model $record) {
                    if (! $state) {
                        return '❌ Sem certificado';
                    }

                    $dataVencimento = Carbon::parse($state);
                    $hoje = Carbon::now();
                    $diasRestantes = round($hoje->diffInDays($dataVencimento, false));

                    if ($diasRestantes < 0) {
                        $diasVencidos = round(abs($diasRestantes));

                        return "❌ Vencido há {$diasVencidos} ".($diasVencidos === 1 ? 'dia' : 'dias');
                    } elseif ($diasRestantes <= 30) {
                        return "⚠️ Vence em {$diasRestantes} ".($diasRestantes === 1 ? 'dia' : 'dias');
                    } else {
                        return '✅ Válido até '.$dataVencimento->format('d/m/Y');
                    }
                })
                ->color(function ($state, Model $record) {
                    if (! $state) {
                        return 'gray';
                    }

                    $dataVencimento = Carbon::parse($state);
                    $hoje = Carbon::now();
                    $diasRestantes = $hoje->diffInDays($dataVencimento, false);

                    if ($diasRestantes < 0) {
                        return 'danger';
                    }
                    if ($diasRestantes <= 30) {
                        return 'warning';
                    }

                    return 'success';
                }),

            TextColumn::make('servicos_habilitados')
                ->label('Serviços')
                ->state(function (Model $record): HtmlString {
                    $html = '<div class="flex items-center gap-2">';
                    $servicos = [
                        'NFe' => $record->nfe_servico,
                        'CTe' => $record->cte_servico,
                        'NFSe' => $record->sync_unecont,
                        'Sieg' => $record->sync_sieg,
                    ];
                    foreach ($servicos as $nome => $habilitado) {
                        $cor = $habilitado ? '#98D8AA' : '#FF6D60';
                        $html .= "<span style='border-radius: 10px; padding:2px 5px 2px 5px; font-size: 11px; background-color: {$cor};'>{$nome}</span>";
                    }
                    $html .= '</div>';

                    return new HtmlString($html);
                })
                ->html()
                ->toggleable(),

            TextColumn::make('created_at')
                ->label('Cadastrada em')
                ->date('d/m/Y H:i')
                ->toggleable(isToggledHiddenByDefault: true)
                ->sortable(),
        ];
    }

    protected static function getRecordActions(): array
    {
        return [
            ActionGroup::make([
                EditAction::make()
                    ->label('Editar Empresa'),

                DownloadCertificadoAction::make(),

                GerenciarServicoAction::make(),

                RemoveCertificadoAction::make(),
            ]),
        ];
    }

    protected static function getFilters(): array
    {
        return [
            SelectFilter::make('is_enabled')
                ->label('Status')
                ->options([
                    1 => 'Ativas',
                    0 => 'Inativas',
                ])
                ->placeholder('Todos os status'),

            Filter::make('certificado_vencendo')
                ->label('Certificado vencendo (30 dias)')
                ->query(function (Builder $query): Builder {
                    return $query->whereNotNull('validade_certificado')
                        ->whereBetween('validade_certificado', [
                            now(),
                            now()->addDays(30),
                        ]);
                }),

            Filter::make('certificado_vencido')
                ->label('Certificado vencido')
                ->query(function (Builder $query): Builder {
                    return $query->whereNotNull('validade_certificado')
                        ->where('validade_certificado', '<', now());
                }),

            Filter::make('sem_certificado')
                ->label('Sem certificado')
                ->query(function (Builder $query): Builder {
                    return $query->whereNull('validade_certificado');
                }),
        ];
    }
}
