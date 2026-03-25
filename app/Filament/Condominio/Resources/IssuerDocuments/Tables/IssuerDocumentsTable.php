<?php

namespace App\Filament\Condominio\Resources\IssuerDocuments\Tables;

use App\Enums\IssuerDocumentTypeEnum;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

class IssuerDocumentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                return $query->where('issuer_id', currentIssuer()->id);
            })
            ->recordUrl(null)
            ->columns([
                TextColumn::make('document_type')
                    ->label('Tipo de Documento')
                    ->formatStateUsing(fn ($state) => IssuerDocumentTypeEnum::tryFrom($state)?->getLabel() ?? $state)
                    ->searchable()
                    ->badge()
                    ->sortable(),

                TextColumn::make('user_name')
                    ->label('Nome do Documento')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('original_name')
                    ->label('Nome Original')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('file_size')
                    ->label('Tamanho')
                    ->formatStateUsing(fn ($state) => $state ? number_format($state / 1024, 2).' KB' : '-')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Data de Envio')
                    ->dateTime('d/m/Y H:i:s')
                    ->sortable(),

                TextColumn::make('validate_at')
                    ->label('Válido até')
                    ->dateTime('d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                Filter::make('document_type')
                    ->label('Tipo de Documento')
                    ->columnSpan(2)
                    ->schema([
                        Select::make('document_type')
                            ->label('Tipo de Documento')
                            ->options(IssuerDocumentTypeEnum::class)
                            ->searchable()
                            ->preload(),
                    ]),

                Filter::make('validate_at')
                    ->label('Vigência')
                    ->columnSpan(2)
                    ->schema([
                        DatePicker::make('de')
                            ->label('Data Validade Início')
                            ->columnSpan(1),
                        DatePicker::make('ate')
                            ->label('Data Validade Final')
                            ->columnSpan(1),
                    ])->columns(2)
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['de'] ?? null,
                                fn (Builder $query, $date): Builder => $query->whereDate('validate_at', '>=', $date),
                            )
                            ->when(
                                $data['ate'] ?? null,
                                fn (Builder $query, $date): Builder => $query->whereDate('validate_at', '<=', $date),
                            );
                    })->indicateUsing(function (array $data): ?string {
                        if (empty($data['de']) && empty($data['ate'])) {
                            return null;
                        }

                        $inicio = $data['de'] ? date('d/m/Y', strtotime($data['de'])) : '...';
                        $fim = $data['ate'] ? date('d/m/Y', strtotime($data['ate'])) : '...';

                        return "Vigência: {$inicio} até {$fim}";
                    }),
            ])
            ->filtersFormColumns(4)
            ->persistFiltersInSession()
            ->deferFilters(true)
            ->recordActions([
                ActionGroup::make([
                    EditAction::make(),
                    Action::make('download')
                        ->label('Download Documento')
                        ->icon(Heroicon::ArrowDown)
                        ->url(fn ($record) => route('issuer-rag.document.show', $record), true),
                    DeleteAction::make()
                        ->before(function ($record) {
                            if ($record->file_path && Storage::disk('local')->exists($record->file_path)) {
                                Storage::disk('local')->delete($record->file_path);
                            }
                        }),

                ]),

            ])
            ->toolbarActions([
                BulkActionGroup::make([]),
            ]);
    }
}
