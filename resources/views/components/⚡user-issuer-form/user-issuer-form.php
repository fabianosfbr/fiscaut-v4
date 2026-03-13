<?php

use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\AttachAction;
use Filament\Actions\DetachAction;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Livewire\Component;



new class extends Component implements HasActions, HasSchemas, HasTable {
    use InteractsWithActions;
    use InteractsWithSchemas;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->relationship(fn () => currentIssuer()->users())
            ->headerActions([
                AttachAction::make()
                    ->recordTitleAttribute('name')
                    ->modalHeading('Vincular Usuário')
                    ->recordSelectOptionsQuery(
                        fn ($query) => $query
                            ->where('tenant_id', currentIssuer()->tenant_id)
                            ->whereDoesntHave('issuers', fn ($q) => $q->where('issuers.id', currentIssuer()->id))
                    ),
            ])
            ->columns([
                TextColumn::make('name')
                    ->label('Nome')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('E-mail')
                    ->searchable(),
                TextColumn::make('pivot.created_at')
                    ->label('Vinculado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->recordActions([
                DetachAction::make(),
            ]);

    }
};
