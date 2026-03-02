<?php

use App\Filament\Forms\Components\SelectTagGrouped;
use App\Models\CategoryTag;
use App\Models\EntradasImpostosEquivalente;
use App\Models\Tag;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

new class extends Component implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions;
    use InteractsWithSchemas;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => EntradasImpostosEquivalente::query()->where('issuer_id', currentIssuer()->id))
            ->defaultSort('created_at', 'desc')
            ->searchDebounce(750)
            ->columns([
                TextColumn::make('tag')
                    ->label('Etiqueta')
                    ->formatStateUsing(function (EntradasImpostosEquivalente $record) {
                        return $record->tag.' - '.$record->tag_description;
                    })
                    ->searchable(),
                TextColumn::make('description')
                    ->label('Descrição')
                    ->searchable('tag_description'),
                ToggleColumn::make('status_icms')
                    ->label('ICMS'),
                ToggleColumn::make('status_ipi')
                    ->label('IPI'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Action::make('add')
                    ->label('Adicionar')
                    ->modalWidth('lg')
                    ->schema(self::getFormSchema())
                    ->action(function (array $data) {

                        $this->updateOrCreate($data);
                    }),
            ])
            ->recordActions([
                Action::make('edit')
                    ->label('Editar')
                    ->icon('heroicon-m-pencil-square')
                    ->modalWidth('lg')
                    ->modalSubmitActionLabel('Salvar')
                    ->modalCancelActionLabel('Cancelar')
                    ->fillForm(function (EntradasImpostosEquivalente $record) {
                        return [
                            'id' => $record->id,
                            'tag' => $record->tag_id,
                            'status_icms' => $record->status_icms,
                            'status_ipi' => $record->status_ipi,

                        ];
                    })
                    ->schema(self::getFormSchema())
                    ->action(function (array $data) {
                        $this->updateOrCreate($data);
                    }),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    //
                ]),
            ]);
    }

    public static function getFormSchema()
    {
        return [
            Hidden::make('id'),
            Toggle::make('status_icms')
                ->label('Modifica ICMS')
                ->default(true),

            Toggle::make('status_ipi')
                ->label('Modifica IPI')
                ->default(true),

            SelectTagGrouped::make('tag')
                ->label('Etiqueta')
                ->multiple(false)
                ->options(CategoryTag::getAllEnabled(currentIssuer()->id)),

        ];
    }

    public function updateOrCreate($data)
    {

        $tag = Tag::find($data['tag']);

        EntradasImpostosEquivalente::updateOrCreate(
            [
                'id' => $data['id'] ?? null,
            ],
            [
                'tag' => $tag->code,
                'tag_id' => $tag->id,
                'tag_description' => $tag->name,
                'description' => 'Zera tag de IPI e/ou ICMS da Nfe',
                'status_icms' => $data['status_icms'],
                'status_ipi' => $data['status_ipi'],
                'issuer_id' => currentIssuer()->id,
                'tenant_id' => Auth::user()->tenant_id,
            ]
        );

        Notification::make()
            ->title('Etiqueta salva com sucesso')
            ->success()
            ->send();
    }
};
