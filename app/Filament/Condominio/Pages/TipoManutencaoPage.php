<?php

namespace App\Filament\Condominio\Pages;

use App\Models\IssuerControl;
use App\Models\TipoManutencao;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Page;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use UnitEnum;


class TipoManutencaoPage extends Page implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions;
    use InteractsWithSchemas;
    use InteractsWithTable;
    protected string $view = 'filament.condominio.pages.tipo-manutencao-page';

    protected static string|UnitEnum|null $navigationGroup = 'Controles';

    protected static ?string $title = 'Tipos de Manutenção';

    protected static ?string $slug = 'tipos-de-manutencao';

    public function table(Table $table): Table
    {
        return $table
            ->query(function () {
                return TipoManutencao::query()->where('tenant_id', currentIssuer()->tenant_id);
            })
            ->columns([
                TextColumn::make('nome')
                    ->label('Nome'),
            ])
            ->filters([
                // ...
            ])
            ->headerActions([
                Action::make('add')
                    ->label('Adicionar')
                    ->modalSubmitActionLabel('Salvar')
                    ->modalCancelActionLabel('Cancelar')
                    ->schema(self::getFormSchema())
                    ->action(function ($data) {
                        self::updateOrCreate($data);
                    })
            ])
            ->recordActions([
                Action::make('edit')
                    ->label('Editar')
                    ->modalSubmitActionLabel('Salvar')
                    ->modalCancelActionLabel('Cancelar')
                    ->modalWidth('md')
                    ->fillForm(function (TipoManutencao $record) {
                        return [
                            'id' => $record?->id,
                            'nome' => $record?->nome ?? null,
                        ];
                    })
                    ->schema(self::getFormSchema())
                    ->action(function ($data, TipoManutencao $record) {
                        self::updateOrCreate($data);
                    }),
                    
            ])
            ->toolbarActions([
                // ...
            ]);
    }

    public static function getFormSchema()
    {
        return [
            Hidden::make('id'),
            TextInput::make('nome')
                ->label('Nome')
                ->required(),
        ];
    }

    public static function updateOrCreate($data)
    {

        TipoManutencao::updateOrCreate(
            [
                'id' => $data['id'] ?? null,
            ],
            [
                'tenant_id' => currentIssuer()->tenant_id,
                'nome' => $data['nome'],
            ]
        );
    }


}
