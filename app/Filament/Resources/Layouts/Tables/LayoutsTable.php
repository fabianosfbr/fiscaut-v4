<?php

namespace App\Filament\Resources\Layouts\Tables;

use App\Models\Layout;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class LayoutsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordUrl(null)
            ->modifyQueryUsing(function (Builder $query) {
                return $query->where('issuer_id', Auth::user()->currentIssuer->id);
            })
            ->columns([
                TextColumn::make('name')
                    ->label('Nome')
                    ->searchable(),
                TextColumn::make('description')
                    ->label('Descrição')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Gerenciar'),

                Action::make('duplicate')
                    ->label('Duplicar')
                    ->icon('heroicon-o-square-2-stack')
                    ->action(function (Layout $record) {

                        $newLayout = $record->duplicateWithRelationships();
                        $newLayout->name = $record->name.' - Cópia';
                        $newLayout->name = $record->description.' - Cópia';
                        $newLayout->save();
                    }),

                DeleteAction::make()
                    ->action(function (array $data, $record, $action) {
                        if ($record->layoutColumns->count() > 0) {
                            Notification::make()
                                ->title('Não é possível excluir o leiaute, pois existem colunas vinculadas a ele.')
                                ->danger()
                                ->send();

                            return;
                        }

                        if ($record->layoutRules->count() > 0) {
                            Notification::make()
                                ->title('Não é possível excluir o leiaute, pois existem regras vinculadas a ele.')
                                ->danger()
                                ->send();

                            return;
                        }

                        Notification::make()
                            ->title('Leiaute excluído com sucesso.')
                            ->success()
                            ->send();

                        $record->delete();
                    }),

            ])
            ->toolbarActions([
                BulkActionGroup::make([]),
            ]);
    }
}
