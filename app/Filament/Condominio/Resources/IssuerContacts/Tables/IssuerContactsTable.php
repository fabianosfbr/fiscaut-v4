<?php

namespace App\Filament\Condominio\Resources\IssuerContacts\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class IssuerContactsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nome')
                    ->label('Nome')
                    ->searchable(),
                TextColumn::make('funcao')
                    ->label('Função')
                    ->badge()
                    ->sortable(),
                TextColumn::make('cpf')
                    ->label('CPF')
                    ->formatStateUsing(fn ($state) => formatar_cnpj_cpf($state)), 
                TextColumn::make('email')
                    ->label('E-mail'),
                TextColumn::make('telefone_whatsapp')
                    ->label('WhatsApp')
                    ->formatStateUsing(function ($state) {
                        $phone = preg_replace('/\D/', '', $state);
                        if (strlen($phone) === 11) {
                            return preg_replace('/(\d{2})(\d{5})(\d{4})/', '($1) $2-$3', $phone);
                        }
                        if (strlen($phone) === 10) {
                            return preg_replace('/(\d{2})(\d{4})(\d{4})/', '($1) $2-$3', $phone);
                        }
                        return $state;
                    }),
                TextColumn::make('unidade')
                    ->label('Unidade'), 
                TextColumn::make('tipo_relacao')
                    ->label('Tipo de Relação')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'isencao' => 'Isenção',
                        'remuneracao' => 'Remuneração',
                        default => $state,
                    }),

            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([]),
            ]);
    }
}
