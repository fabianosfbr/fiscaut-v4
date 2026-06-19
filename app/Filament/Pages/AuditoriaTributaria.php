<?php

namespace App\Filament\Pages;

use App\Models\NfeValidacaoTributaria;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\DatePicker;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use UnitEnum;

class AuditoriaTributaria extends Page implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions;
    use InteractsWithSchemas;
    use InteractsWithTable;

    protected static ?string $navigationLabel = 'Auditoria Tributária';

    protected static ?string $title = 'Auditoria Tributária';

    protected static ?string $slug = 'auditoria-tributaria';

    protected static string|UnitEnum|null $navigationGroup = 'NFe';

    protected static ?int $navigationSort = 5;

    protected string $view = 'filament.pages.auditoria-tributaria';

    public function table(Table $table): Table
    {
        return $table
            ->recordUrl(null)
            ->query(function () {
                $issuer = currentIssuer();

                return NfeValidacaoTributaria::query()
                    ->where('issuer_id', $issuer->id)
                    ->with('nfe')
                    ->orderBy('created_at', 'desc');
            })
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('nfe.nNF')
                    ->label('NF-e')
                    ->searchable(),

                TextColumn::make('nfe.emitente_razao_social')
                    ->label('Emitente')
                    ->limit(30)
                    ->searchable(),

                TextColumn::make('nfe.chave')
                    ->label('Chave')
                    ->limit(20)
                    ->searchable(),

                TextColumn::make('regra')
                    ->label('Regra')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('tipo_imposto')
                    ->label('Imposto')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('n_item')
                    ->label('Item'),

                TextColumn::make('mensagem')
                    ->label('Mensagem')
                    ->limit(60)
                    ->searchable()
                    ->wrap(),

                TextColumn::make('valor_esperado')
                    ->label('Esperado'),

                TextColumn::make('valor_encontrado')
                    ->label('Encontrado'),

                TextColumn::make('severidade')
                    ->label('Severidade')
                    ->badge(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge(),

                TextColumn::make('created_at')
                    ->label('Data')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('severidade')
                    ->label('Severidade')
                    ->options([
                        'info' => 'Informativo',
                        'aviso' => 'Aviso',
                        'erro' => 'Erro',
                    ])
                    ->multiple(),

                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pendente' => 'Pendente',
                        'confirmado' => 'Confirmado',
                        'ignorado' => 'Ignorado',
                    ])
                    ->multiple(),

                SelectFilter::make('regra')
                    ->label('Regra')
                    ->options(fn () => NfeValidacaoTributaria::query()
                        ->where('issuer_id', currentIssuer()->id)
                        ->distinct('regra')
                        ->pluck('regra', 'regra')
                        ->toArray())
                    ->multiple(),

                Filter::make('created_at')
                    ->label('Data da Validação')
                    ->form([
                        DatePicker::make('data_inicio')
                            ->label('Data Início'),
                        DatePicker::make('data_fim')
                            ->label('Data Fim'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (! empty($data['data_inicio'])) {
                            $query->whereDate('created_at', '>=', $data['data_inicio']);
                        }
                        if (! empty($data['data_fim'])) {
                            $query->whereDate('created_at', '<=', $data['data_fim']);
                        }

                        return $query;
                    }),
            ])
            ->filtersFormColumns(4)
            ->deferFilters(true)
            ->recordActions([
                Action::make('confirmar')
                    ->label('Confirmar')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn (NfeValidacaoTributaria $record): bool => $record->status === 'pendente')
                    ->action(function (NfeValidacaoTributaria $record) {
                        $record->update([
                            'status' => 'confirmado',
                            'resolved_at' => now(),
                            'resolved_by' => auth()->id(),
                        ]);

                        Notification::make()
                            ->title('Inconsistência confirmada')
                            ->success()
                            ->send();
                    }),

                Action::make('ignorar')
                    ->label('Ignorar')
                    ->icon('heroicon-o-x-mark')
                    ->color('gray')
                    ->visible(fn (NfeValidacaoTributaria $record): bool => $record->status === 'pendente')
                    ->requiresConfirmation()
                    ->action(function (NfeValidacaoTributaria $record) {
                        $record->update([
                            'status' => 'ignorado',
                            'resolved_at' => now(),
                            'resolved_by' => auth()->id(),
                        ]);

                        Notification::make()
                            ->title('Inconsistência ignorada')
                            ->warning()
                            ->send();
                    }),
            ])
            ->bulkActions([
                BulkAction::make('confirmar_todas')
                    ->label('Confirmar Selecionadas')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (Collection $records) {
                        $count = $records->where('status', 'pendente')->each->update([
                            'status' => 'confirmado',
                            'resolved_at' => now(),
                            'resolved_by' => auth()->id(),
                        ])->count();

                        Notification::make()
                            ->title("{$count} inconsistência(s) confirmada(s)")
                            ->success()
                            ->send();
                    }),

                BulkAction::make('ignorar_todas')
                    ->label('Ignorar Selecionadas')
                    ->icon('heroicon-o-x-mark')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->action(function (Collection $records) {
                        $count = $records->where('status', 'pendente')->each->update([
                            'status' => 'ignorado',
                            'resolved_at' => now(),
                            'resolved_by' => auth()->id(),
                        ])->count();

                        Notification::make()
                            ->title("{$count} inconsistência(s) ignorada(s)")
                            ->warning()
                            ->send();
                    }),
            ]);
    }
}
