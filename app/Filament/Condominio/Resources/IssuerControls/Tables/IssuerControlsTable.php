<?php

namespace App\Filament\Condominio\Resources\IssuerControls\Tables;

use App\Filament\Condominio\Resources\IssuerControls\IssuerControlResource;
use App\Models\IssuerControl;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;

class IssuerControlsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function ($query) {
                $issuerId = currentIssuer()->id;

                $grouped = IssuerControl::query()
                    ->where('issuer_controls.issuer_id', $issuerId)
                    ->leftJoin('issuer_control_fields', 'issuer_control_fields.id', '=', 'issuer_controls.issuer_control_field_id')
                    ->leftJoin('issuer_group_controls', 'issuer_group_controls.id', '=', 'issuer_control_fields.issuer_group_control_id')
                    ->select([
                        'issuer_controls.issuer_id',
                        'issuer_control_fields.issuer_group_control_id as group_id',
                        'issuer_group_controls.name as group_name',
                        DB::raw('COUNT(issuer_controls.id) as saved_count'),
                        DB::raw('MAX(issuer_controls.updated_at) as updated_at'),
                        DB::raw('MAX(issuer_controls.id) as id'),
                    ])
                    ->groupBy('issuer_controls.issuer_id', 'group_id', 'group_name');

                return $query
                    ->fromSub($grouped, 'issuer_controls')
                    ->select('*')
                    ->reorder();
            })
            ->recordUrl(null)
            ->columns([
                TextColumn::make('group_name')
                    ->label('Grupo')
                    ->placeholder('—'),

                TextColumn::make('saved_count')
                    ->label('Atributos salvos')
                    ->numeric(),

                TextColumn::make('updated_at')
                    ->label('Última atualização')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('updated_at', 'desc')
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make()
                    ->url(fn ($record) => IssuerControlResource::getUrl('edit-group', ['groupId' => $record->group_id])),
            ])
            ->toolbarActions([
                BulkActionGroup::make([]),
            ]);
    }
}
