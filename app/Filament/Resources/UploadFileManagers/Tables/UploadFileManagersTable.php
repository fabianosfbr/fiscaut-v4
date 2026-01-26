<?php

namespace App\Filament\Resources\UploadFileManagers\Tables;

use App\Enums\DocTypeEnum;
use App\Filament\Resources\UploadFileManagers\Actions\DownloadFileAction;
use App\Filament\Tables\Columns\TagDocsColumn;
use App\Jobs\DownloadLoteUploadFile;
use App\Models\GeneralSetting;
use App\Models\Tag;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Support\Enums\Alignment;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class UploadFileManagersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->paginated([10, 25, 50, 100])
            ->modifyQueryUsing(function ($query) {
                $query->where('issuer_id', Auth::user()->currentIssuer->id);
            })
            ->recordUrl(null)
            ->columns([
                TextColumn::make('id')
                    ->label('Nº')
                    ->sortable(),
                TextColumn::make('title')
                    ->label('Descrição')
                    ->searchable()
                    ->limit(40)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();

                        if (strlen($state) <= 40) {
                            return null;
                        }

                        // Only render the tooltip if the column contents exceeds the length limit.
                        return $state;
                    }),
                TextColumn::make('doc_value')
                    ->label('Valor')
                    ->money('BRL'),

                TextColumn::make('doc_type')
                    ->label('Tipo')
                    ->badge(),
                TagDocsColumn::make('tagged')
                    ->label('Etiqueta')
                    ->showTagCode(function () {
                        $currentIssuerId = Auth::user()->currentIssuer->id;

                        return GeneralSetting::getValue(
                            name: 'configuracoes_gerais',
                            key: 'isNfeMostrarCodigoEtiqueta',
                            default: false,
                            issuerId: $currentIssuerId
                        );
                    }),

                IconColumn::make('processed')
                    ->label('Apurado')
                    ->tooltip('Indica se a nota foi escriturada pelo departamento contábil/fiscal')
                    ->boolean()
                    ->alignment(Alignment::Center),
                TextColumn::make('created_at')
                    ->label('Data Envio')
                    ->dateTime('d/m/y'),
                TextColumn::make('periodo_exercicio')
                    ->label('Exercício')
                    ->date('F - Y'),
            ])
            ->filters([

                SelectFilter::make('processed')
                    ->label('Apurado')
                    ->options([
                        '1' => 'Sim',
                        '0' => 'Não',
                    ])
                    ->columnSpan(1),

                SelectFilter::make('periodo_exercicio')
                    ->label('Período')
                    ->options(getMesesAnterioresEPosteriores()),

                SelectFilter::make('doc_type')
                    ->label('Tipo')
                    ->options(DocTypeEnum::class),

                Filter::make('qtde_etiqueta')
                    ->schema([
                        Select::make('num_etiquetas')
                            ->label('Nº de etiquetas')
                            ->options([
                                'Sem etiqueta' => 'Sem etiqueta',
                                'Apenas uma etiqueta' => 'Apenas uma etiqueta',
                                'Multiplas etiquetas' => 'Multiplas etiquetas',

                            ]),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query->when($data['num_etiquetas'], function ($q) use ($data) {
                            if ($data['num_etiquetas'] == 'Sem etiqueta') {
                                $q->whereHas('tagged', operator: '=', count: 0);
                            } elseif ($data['num_etiquetas'] == 'Apenas uma etiqueta') {
                                $q->whereHas('tagged', operator: '=', count: 1);
                            } elseif ($data['num_etiquetas'] == 'Multiplas etiquetas') {
                                $q->whereHas('tagged', operator: '>', count: 1);
                            }
                        });
                    })->indicateUsing(function (array $data): ?string {

                        if (! $data['num_etiquetas']) {
                            return null;
                        }

                        return 'Nº de etiquetas: ' . $data['num_etiquetas'];
                    }),

                Filter::make('etiquetas')
                    ->label('Etiquetas')
                    ->schema([
                        CheckboxList::make('etiquetas')
                            ->label('Etiquestas')
                            ->bulkToggleable()
                            ->searchable()
                            ->columns(2)
                            ->options(Tag::getTagsUsedInUploadFile()),
                    ])->columnSpan(2)
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when($data['etiquetas'], function ($q) use ($data) {
                            $values = $data['etiquetas'];
                            $q->whereHas('tagged', fn($query) => $query->whereIn('tag_id', $values));
                        });
                    })
                    ->columnSpan('full'),
            ])
            ->filtersFormMaxHeight('500px')
            ->filtersFormColumns(4)
            ->recordActions([
                ActionGroup::make([
                    EditAction::make(),
                    DownloadFileAction::make()
                        ->color('primary'),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([

                    BulkAction::make('apurar')
                        ->label('Apurar')
                        ->icon(Heroicon::CurrencyDollar)
                        ->modalSubmitActionLabel('Sim, apurar')
                        ->visible(function () {
                            $user = Auth::user();
                            if ($user->hasRole('super-admin', 'admin', 'contabilidade') && $user->hasPermission('marcar-documento-como-apurado')) {
                                return true;
                            }

                            return false;
                        })

                        ->requiresConfirmation()
                        ->action(function (Collection $records) {
                            foreach ($records as $record) {
                                /** @var \App\Models\UploadFileManager $record */
                                $record->update([
                                    'processed' => ! $record->processed,
                                ]);
                            }
                        }),

                    BulkAction::make('remover')
                        ->label('Remover')
                        ->icon(Heroicon::Trash)
                        ->modalSubmitActionLabel('Sim, remover')
                        ->requiresConfirmation()
                        ->action(function (Collection $records) {
                            foreach ($records as $record) {
                                /** @var \App\Models\UploadFileManager $record */
                                if (! $record->processed) {
                                    Storage::delete($record->path);
                                    $record->delete();
                                }
                            }
                        }),

                    BulkAction::make('download-docs')
                        ->label('Download Docs')
                        ->icon(Heroicon::ArrowDown)
                        ->modalWidth('sm')
                        ->modalHeading('Download de documentos')
                        ->modalDescription('Selecione as opção de download que deseja.')
                        ->schema([
                            Checkbox::make('is_folder')
                                ->label('Organizar por tipo de documento')
                                ->inline(),
                        ])
                        ->action(function (Collection $records, array $data) {
                            //  DownloadLoteUploadFile::dispatch($records, $data, Auth::user());

                            Notification::make()
                                ->success()
                                ->title('Download Iniciado')
                                ->body('O arquivo ZIP está sendo gerado. Você receberá uma notificação quando estiver pronto.')
                                ->send();
                        }),
                ]),
            ]);
    }
}
