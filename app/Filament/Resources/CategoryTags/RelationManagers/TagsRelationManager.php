<?php

namespace App\Filament\Resources\CategoryTags\RelationManagers;

use App\Models\Tag;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class TagsRelationManager extends RelationManager
{
    protected static string $relationship = 'tags';

    protected static ?string $title = 'Etiquetas';

    protected static ?string $recordTitleAttribute = 'name';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(2)
                    ->schema([
                        TextInput::make('code')
                            ->label('Código')
                            ->maxLength(25)
                            ->unique(
                                table: Tag::class,
                                column: 'code',
                                ignoreRecord: true,
                                modifyRuleUsing: fn ($rule) => $rule->where('issuer_id', Auth::user()->currentIssuer->id)
                            )
                            ->columnSpan(2),

                        TextInput::make('name')
                            ->label('Nome')
                            ->required()
                            ->maxLength(125)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (callable $set, $state) {
                                if ($state) {
                                    $set('slug', Str::slug($state));
                                }
                            })
                            ->columnSpan(2),

                        Hidden::make('slug')
                            ->required(),
                        Toggle::make('is_enable')
                            ->label('Ativo')
                            ->default(true)
                            ->columnSpan(1),

                        // Campos hidden que serão preenchidos automaticamente
                        Hidden::make('tenant_id'),
                        Hidden::make('issuer_id'),
                        Hidden::make('category_id'),
                    ])
                    ->columnSpanFull(),

            ])
            ->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('code')
                    ->label('Código')
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Nome')
                    ->sortable(),
                IconColumn::make('is_enable')
                    ->label('Habilitada')
                    ->boolean(),
            ])
            ->filters([
                TernaryFilter::make('is_enable')
                    ->label('Status')
                    ->trueLabel('Apenas ativos')
                    ->falseLabel('Apenas inativos')
                    ->placeholder('Todos'),

            ])
            ->headerActions([
                CreateAction::make()
                    ->modalWidth(Width::Small)
                    ->label('Nova Etiqueta')
                    ->modalHeading('Nova Etiqueta')
                    ->mutateDataUsing(function (array $data): array {

                        // Preenche campos de controle automaticamente
                        $user = Auth::user();

                        if ($user && $user->tenant_id && $user->currentIssuer) {
                            $data['tenant_id'] = $user->tenant_id;
                            $data['issuer_id'] = $user->currentIssuer->id;
                            $data['category_id'] = $this->getOwnerRecord()->id;
                        } else {
                            throw new \Exception('Usuário não autenticado ou empresa não selecionada');
                        }

                        // Gera slug se não existe
                        if (! isset($data['slug']) || ! $data['slug']) {
                            $data['slug'] = Str::slug($data['name']);
                        }

                        return $data;
                    })
                    ->after(function (CreateAction $action, array $data) {
                        Cache::forget('category_tag_.'.Auth::user()->currentIssuer->id.'._all');
                    }),
            ])
            ->recordActions([
                EditAction::make()
                    ->modalWidth(Width::Small)
                    ->modalHeading('Editar Etiqueta'),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('name', 'asc')
            ->modifyQueryUsing(function (Builder $query): Builder {
                // Aplica filtros de segurança apenas se o usuário estiver autenticado
                $user = Auth::user();

                if ($user && $user->tenant_id && $user->currentIssuer) {
                    $query->where('tenant_id', $user->tenant_id)
                        ->where('issuer_id', $user->currentIssuer->id);
                }

                return $query;
            });
    }
}
