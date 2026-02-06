<?php

namespace App\Filament\Resources\XmlImportJobs\Tables;

use App\Models\XmlImportJob;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class XmlImportJobsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->recordUrl(null)
            ->columns([
                TextColumn::make('created_at')
                    ->label('Data de Criação')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('owner_display_name')
                    ->label('Usuário Responsável')
                    ->getStateUsing(function ($record) {
                        if (! $record->owner) {
                            return 'N/A';
                        }

                        // Se o owner é um User, usa o campo 'name'
                        if ($record->owner instanceof \App\Models\User) {
                            return $record->owner->name;
                        }

                        // Se o owner é um Issuer, usa o campo 'razao_social'
                        if ($record->owner instanceof \App\Models\Issuer) {
                            return Str::limit($record->owner->razao_social, 25);
                        }

                        // Fallback para outros tipos
                        return $record->owner->name ?? $record->owner->razao_social ?? 'Desconhecido';
                    })
                    ->searchable(query: function ($query, $search) {
                        return $query->where(function ($query) use ($search) {
                            $query->whereHasMorph('owner', [\App\Models\User::class], function ($query) use ($search) {
                                $query->where('name', 'like', "%{$search}%");
                            })
                                ->orWhereHasMorph('owner', [\App\Models\Company::class], function ($query) use ($search) {
                                    $query->where('razao_social', 'like', "%{$search}%");
                                });
                        });
                    })
                    ->sortable(query: function ($query, $direction) {
                        return $query->orderBy(
                            \App\Models\User::select('name')
                                ->whereColumn('users.id', 'xml_import_jobs.owner_id')
                                ->where('xml_import_jobs.owner_type', \App\Models\User::class)
                                ->union(
                                    \App\Models\Company::select('razao_social')
                                        ->whereColumn('companies.id', 'xml_import_jobs.owner_id')
                                        ->where('xml_import_jobs.owner_type', \App\Models\Company::class)
                                ),
                            $direction
                        );
                    }),

                TextColumn::make('import_type')
                    ->label('Tipo')
                    ->badge()
                    ->searchable(),
                TextColumn::make('total_files')
                    ->label('Total de Arquivos')
                    ->sortable(),
                TextColumn::make('num_documents')
                    ->label('Documentos')
                    ->sortable(),
                TextColumn::make('num_events')
                    ->label('Eventos')
                    ->sortable(),
                TextColumn::make('error_files')
                    ->label('Com Erro')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'warning' => XmlImportJob::STATUS_PENDING,
                        'primary' => XmlImportJob::STATUS_PROCESSING,
                        'success' => XmlImportJob::STATUS_COMPLETED,
                        'danger' => XmlImportJob::STATUS_FAILED,
                    ])
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        XmlImportJob::STATUS_PENDING => 'Pendente',
                        XmlImportJob::STATUS_PROCESSING => 'Processando',
                        XmlImportJob::STATUS_COMPLETED => 'Concluído',
                        XmlImportJob::STATUS_FAILED => 'Falhou',
                        default => $state,
                    })
                    ->badge(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([]);
    }
}
