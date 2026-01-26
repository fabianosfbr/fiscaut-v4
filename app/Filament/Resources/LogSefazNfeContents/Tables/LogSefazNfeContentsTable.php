<?php

namespace App\Filament\Resources\LogSefazNfeContents\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class LogSefazNfeContentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordUrl(null)
            ->modifyQueryUsing(fn ($query) => $query->where('issuer_id', Auth::user()->currentIssuer->id))
            ->columns([
                TextColumn::make('nsu')
                    ->label('NSU')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('max_nsu')
                    ->label('NSU Máximo')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('created_at')
                    ->label('Criado em')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => $state->format('d/m/Y H:i:s')),
            ])
            ->filters([
                //
            ])
            ->recordActions([])
            ->toolbarActions([
                BulkActionGroup::make([]),
            ]);
    }
}
