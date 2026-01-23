<?php

namespace App\Filament\Resources\CategoryTags\Tables;

use App\Filament\Resources\CategoryTags\CategoryTagResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Support\Colors\Color;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class CategoryTagsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query): Builder {

                $user = Auth::user();

                if ($user && $user->tenant_id && $user->currentIssuer) {
                    $query->where('tenant_id', $user->tenant_id)
                        ->where('issuer_id', $user->currentIssuer->id);
                } else {
                    // Se não há usuário autenticado ou issuer, retorna query vazia
                    $query->whereRaw('1 = 0');
                }

                return $query;
            })
            ->recordUrl(null)
            ->defaultSort('order', 'asc')

            ->columns([
                TextColumn::make('order')
                    ->label('Ordem')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('name')
                    ->label('Nome')
                    ->sortable()
                    ->searchable(),
                IconColumn::make('is_difal')
                    ->label('Difal')
                    ->boolean(),
                IconColumn::make('is_devolucao')
                    ->label('Devolução')
                    ->boolean(),
                TextColumn::make('color')
                    ->label('Cor')
                    ->formatStateUsing(fn ($state) => $state ? '#'.ltrim($state, '#') : 'Sem cor')
                    ->badge()
                    ->copyable()
                    ->copyMessage('Cor copiada para área de transferência')
                    ->color(fn ($state) => $state ? Color::hex($state) : 'gray')
                    ->icon('heroicon-m-swatch')
                    ->iconColor(fn ($state) => $state ? Color::hex($state) : 'gray'),
                IconColumn::make('is_enable')
                    ->label('Ativo')
                    ->boolean(),
                TextColumn::make('tipo_item')
                    ->label('Tipo de Item')
                    ->formatStateUsing(fn ($state) => $state ? config('tags.tipo_item')[$state] : 'Sem tipo')
                    ->badge()
                    ->color('info'),
                TextColumn::make('tags_count')
                    ->label('Nº Etiquetas')
                    ->counts('tags')
                    ->badge()
                    ->color('info'),

            ])
            ->filters([
                Filter::make('busca_geral')
                    ->label('Buscar por Etiqueta, Nome ou Código')
                    ->schema([
                        TextInput::make('search')
                            ->label('Busca')
                            ->placeholder('Digite nome da categoria, nome da etiqueta ou código...')
                            ->columnSpanFull(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (empty($data['search'])) {
                            return $query;
                        }

                        $search = '%'.$data['search'].'%';

                        return $query->where(function (Builder $query) use ($search, $data) {
                            // Busca no nome da categoria
                            $query->where('name', 'like', $search)
                                // Busca no grupo da categoria (se numérico, busca exata)
                                ->orWhere('grupo', '=', is_numeric(trim($data['search'])) ? trim($data['search']) : null)
                                // Busca nas etiquetas relacionadas (nome e código)
                                ->orWhereHas('tags', function (Builder $query) use ($search) {
                                    $query->where('name', 'like', $search)
                                        ->orWhere('code', 'like', $search);
                                });
                        });
                    })
                    ->indicateUsing(function (array $data): ?string {
                        if (empty($data['search'])) {
                            return null;
                        }

                        return 'Busca: '.$data['search'];
                    }),

                SelectFilter::make('tags')
                    ->label('Filtrar por Etiqueta')
                    ->relationship('tags', 'name')
                    ->searchable()
                    ->preload()
                    ->multiple()
                    ->placeholder('Selecione etiquetas...')
                    ->query(function (Builder $query, array $data): Builder {
                        if (empty($data['values'])) {
                            return $query;
                        }

                        return $query->whereHas('tags', function (Builder $query) use ($data) {
                            $query->whereIn('id', $data['values']);
                        });
                    }),

                TernaryFilter::make('is_enable')
                    ->label('Status')
                    ->placeholder('Todos')
                    ->trueLabel('Apenas Ativos')
                    ->falseLabel('Apenas Inativos'),

                TernaryFilter::make('is_difal')
                    ->label('DIFAL')
                    ->placeholder('Todos')
                    ->trueLabel('Apenas DIFAL')
                    ->falseLabel('Sem DIFAL'),

                TernaryFilter::make('is_devolucao')
                    ->label('Devolução')
                    ->placeholder('Todos')
                    ->trueLabel('Apenas Devolução')
                    ->falseLabel('Sem Devolução'),

                SelectFilter::make('tipo_item')
                    ->label('Tipo de Item')
                    ->options([
                        1 => 'Mercadoria para Revenda',
                        2 => 'Matéria-Prima',
                        3 => 'Embalagem',
                        4 => 'Produto em Processo',
                        5 => 'Produto Acabado',
                        6 => 'Subproduto',
                        7 => 'Produto Intermediário',
                        8 => 'Material de Uso e Consumo',
                        9 => 'Ativo Imobilizado',
                        10 => 'Outros',
                    ])
                    ->placeholder('Todos os tipos'),

            ])
            ->recordActions([
                // EditAction::make(),
                Action::make('manage_tags')
                    ->label('Editar')
                    ->icon('heroicon-o-tag')
                    ->url(fn (Model $record): string => CategoryTagResource::getUrl('edit', ['record' => $record->id]).'?activeRelationManagerTab=0')
                    ->tooltip('Gerenciar etiquetas desta categoria'),
            ])
            ->toolbarActions([]);
    }
}
