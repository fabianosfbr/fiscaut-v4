<?php

namespace App\Filament\Resources\XmlImportJobs\Schemas;

use App\Models\XmlImportJob;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class XmlImportJobInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informações da Importação')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('id')
                                    ->label('ID'),
                                TextEntry::make('created_at')
                                    ->label('Data de Criação')
                                    ->dateTime('d/m/Y H:i'),
                                TextEntry::make('owner_display_name')
                                    ->label('Responsável')
                                    ->getStateUsing(function ($record) {
                                        if (! $record->owner) {
                                            return 'N/A';
                                        }

                                        // Se o owner é um User, usa o campo 'name'
                                        if ($record->owner instanceof \App\Models\User) {
                                            return $record->owner->name;
                                        }

                                        // Se o owner é um Issuer, usa o campo 'razao_social'
                                        if ($record->owner instanceof \App\Models\Company) {
                                            return $record->owner->razao_social;
                                        }

                                        // Fallback para outros tipos
                                        return $record->owner->name ?? $record->owner->razao_social ?? 'Desconhecido';
                                    }),
                            ]),
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('status')
                                    ->label('Status')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        XmlImportJob::STATUS_PENDING => 'warning',
                                        XmlImportJob::STATUS_PROCESSING => 'primary',
                                        XmlImportJob::STATUS_COMPLETED => 'success',
                                        XmlImportJob::STATUS_FAILED => 'danger',
                                        default => 'gray',
                                    })
                                    ->formatStateUsing(fn (string $state): string => match ($state) {
                                        XmlImportJob::STATUS_PENDING => 'Pendente',
                                        XmlImportJob::STATUS_PROCESSING => 'Processando',
                                        XmlImportJob::STATUS_COMPLETED => 'Concluído',
                                        XmlImportJob::STATUS_FAILED => 'Falhou',
                                        default => $state,
                                    }),
                                TextEntry::make('import_type')
                                    ->label('Tipo')
                                    ->badge(),
                            ]),
                    ])->columnSpanFull(),
                Section::make('Estatísticas')
                    ->schema([
                        Grid::make(4)
                            ->schema([
                                TextEntry::make('total_files')
                                    ->label('Total de Arquivos'),
                                TextEntry::make('error_files')
                                    ->label('Com Erro'),
                                TextEntry::make('num_documents')
                                    ->label('Documentos'),
                                TextEntry::make('num_events')
                                    ->label('Eventos'),
                            ]),
                    ])->columnSpanFull(),

            ]);
    }
}
