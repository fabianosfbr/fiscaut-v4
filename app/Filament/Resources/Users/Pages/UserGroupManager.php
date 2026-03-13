<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\Role;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Str;


class UserGroupManager extends Page implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions;
    use InteractsWithSchemas;
    use InteractsWithTable;
    protected static string $resource = UserResource::class;

    protected static ?string $title = 'Grupos de Usuários';

    protected string $view = 'filament.resources.users.pages.user-group-manager';


    protected function getHeaderActions(): array
    {
        return [
            Action::make('index')
                ->label('Voltar para a lista')
                ->url(static::getResource()::getUrl()),

        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordUrl(null)
            ->query(Role::query())
            ->headerActions([
                Action::make('create')
                    ->label('Criar Novo Grupo')
                    ->modalWidth('md')
                    ->modalHeading('Criar Novo Grupo de Usuários')
                    ->modalSubmitActionLabel('Salvar')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nome do Grupo')
                            ->required()
                            ->unique(Role::class, 'name'),
                    ])
                    ->action(function (array $data) {
                        $data['tenant_id'] = currentIssuer()->tenant_id;
                        $data['slug'] = Str::slug($data['name']);
                        Role::create($data);
                    }),
            ])
            ->columns([
                TextColumn::make('name')
                    ->label('Nome do Grupo'),
                TextColumn::make('users_count')
                    ->label('Número de Usuários')
                    ->counts('users'),
            ])
            ->filters([
                // ...
            ])
            ->recordActions([
                EditAction::make('edit')
                    ->label('Editar')
                    ->modalWidth('md')
                    ->modalHeading('Editar Grupo de Usuários')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nome do Grupo')
                            ->required()
                            ->unique(Role::class, 'name', ignoreRecord: true),
                    ]),
                DeleteAction::make('delete')
                    ->label('Excluir')
                    ->modalHeading('Confirmar Exclusão')
                    ->modalSubheading('Tem certeza de que deseja excluir este grupo de usuários? Esta ação não pode ser desfeita.')
                    ->before(function (DeleteAction $action, Role $record) {

                        if ($record->users()->count() > 0) {
                            Notification::make()
                                ->title('Não é possível excluir o grupo')
                                ->body('Este grupo possui usuários vinculados. Remova todos os usuários antes de excluir o grupo.')
                                ->danger()
                                ->duration(2000)
                                ->send();

                            $action->cancel();
                        }
                    }),

            ])
            ->toolbarActions([
                // ...
            ]);
    }
}
