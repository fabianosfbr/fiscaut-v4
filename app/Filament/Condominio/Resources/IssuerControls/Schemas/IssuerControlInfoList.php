<?php

namespace App\Filament\Condominio\Resources\IssuerControls\Schemas;

use App\Enums\IssuerControlPriorityEnum;
use App\Enums\IssuerControlStatusEnum;
use App\Filament\Infolists\Components\IssuerControlLogEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;

class IssuerControlInfoList
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Tabs::make('Detalhes da Manutenção')
                    ->columnSpanFull()
                    ->tabs([
                        Tabs\Tab::make('Resumo')
                            ->id('resumo')
                            ->schema([
                                Section::make()
                                    ->columns(3)
                                    ->schema([
                                        TextEntry::make('titulo')
                                            ->label('Título')
                                            ->weight('bold')
                                            ->columnSpan(2),
                                        Grid::make(3)
                                            ->schema([
                                                TextEntry::make('status')
                                                    ->label('Status')
                                                    ->badge()
                                                    ->icon(fn (?IssuerControlStatusEnum $state): ?string => $state?->getIcon())
                                                    ->color(fn (?IssuerControlStatusEnum $state): string => $state?->getColor() ?? 'gray')
                                                    ->formatStateUsing(fn (?IssuerControlStatusEnum $state): string => $state?->getLabel() ?? ''),

                                                TextEntry::make('prioridade')
                                                    ->label('Prioridade')
                                                    ->badge()
                                                    ->icon(fn (?IssuerControlPriorityEnum $state): ?string => $state?->getIcon())
                                                    ->color(fn (?IssuerControlPriorityEnum $state): string => $state?->getColor() ?? 'gray')
                                                    ->formatStateUsing(fn (?IssuerControlPriorityEnum $state): string => $state?->getLabel() ?? '\u2014'),

                                                TextEntry::make('tipo')
                                                    ->label('Tipo')
                                                    ->badge(),
                                            ]),
                                    ]),
                                Section::make('Programação e Execução')
                                    ->description('Acompanhe as datas principais e o andamento.')
                                    ->schema([
                                        Grid::make(4)
                                            ->schema([
                                                TextEntry::make('data_programada')
                                                    ->label('Programada')
                                                    ->date('d/m/Y')
                                                    ->icon('heroicon-m-calendar')
                                                    ->weight('bold'),

                                                TextEntry::make('data_execucao')
                                                    ->label('Início')
                                                    ->dateTime('d/m/Y H:i')
                                                    ->icon('heroicon-m-play')
                                                    ->placeholder('Não definido'),

                                                TextEntry::make('data_conclusao')
                                                    ->label('Conclusão')
                                                    ->dateTime('d/m/Y H:i')
                                                    ->icon('heroicon-m-check-circle')
                                                    ->placeholder('Não definido'),

                                                TextEntry::make('dias_atraso')
                                                    ->label('Atraso')
                                                    ->suffix(' dia(s)')
                                                    ->color(fn (int $state): string => $state > 0 ? 'danger' : 'gray')
                                                    ->visible(fn ($record): bool => ($record->dias_atraso ?? 0) > 0),
                                            ]),
                                    ])
                                    ->collapsible(),

                                Section::make('Local e Responsáveis')
                                    ->schema([
                                        Grid::make(3)
                                            ->schema([
                                                TextEntry::make('usuario_responsavel')
                                                    ->label('Responsável')
                                                    ->icon('heroicon-m-user')
                                                    ->placeholder('Não definido'),

                                                TextEntry::make('local')
                                                    ->label('Local')
                                                    ->icon('heroicon-m-map-pin')
                                                    ->placeholder('Não definido'),

                                                TextEntry::make('equipamento')
                                                    ->label('Equipamento')
                                                    ->icon('heroicon-m-cog-6-tooth')
                                                    ->placeholder('Não definido'),
                                            ]),
                                    ])
                                    ->collapsible(),
                            ]),
                        Tabs\Tab::make('Detalhes')
                            ->id('detalhes')
                            ->schema([
                                Section::make('Informações básicas')
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                TextEntry::make('typeControl.nome')
                                                    ->label('Tipo de Controle')
                                                    ->placeholder('Não definido'),

                                                TextEntry::make('recorrencia.titulo_template')
                                                    ->label('Recorrência')
                                                    ->placeholder('Não definido')
                                                    ->visible(fn ($record): bool => filled($record->recorrencia_id)),
                                            ]),
                                    ])
                                    ->collapsible(),

                                Section::make('Descrição')
                                    ->schema([
                                        TextEntry::make('descricao')
                                            ->label('Descrição')
                                            ->markdown()
                                            ->placeholder('Sem descrição.'),
                                    ])
                                    ->collapsible(),

                                Section::make('Custos e indicadores')
                                    ->schema([
                                        Grid::make(3)
                                            ->schema([
                                                TextEntry::make('custo_estimado')
                                                    ->label('Estimado')
                                                    ->money('BRL')
                                                    ->placeholder('Não definido'),

                                                TextEntry::make('custo_real')
                                                    ->label('Real')
                                                    ->money('BRL')
                                                    ->placeholder('Não definido'),

                                                TextEntry::make('variacao_custo')
                                                    ->label('Variação')
                                                    ->suffix('%')
                                                    ->formatStateUsing(function ($state): string {
                                                        if ($state === null) {
                                                            return 'Não definido';
                                                        }

                                                        return number_format((float) $state, 2, ',', '.');
                                                    })
                                                    ->color(function ($state): string {
                                                        if ($state === null) {
                                                            return 'gray';
                                                        }

                                                        if ((float) $state > 0) {
                                                            return 'danger';
                                                        }

                                                        if ((float) $state < 0) {
                                                            return 'success';
                                                        }

                                                        return 'gray';
                                                    }),

                                                TextEntry::make('duracao')
                                                    ->label('Duração')
                                                    ->formatStateUsing(function ($state): string {
                                                        if (! $state) {
                                                            return 'Não definido';
                                                        }

                                                        $minutes = (int) $state;
                                                        $hours = intdiv($minutes, 60);
                                                        $remainingMinutes = $minutes % 60;

                                                        if ($hours <= 0) {
                                                            return "{$remainingMinutes} min";
                                                        }

                                                        if ($remainingMinutes <= 0) {
                                                            return "{$hours} h";
                                                        }

                                                        return "{$hours} h {$remainingMinutes} min";
                                                    }),
                                            ]),
                                    ])
                                    ->collapsible(),

                                Section::make('Observações')
                                    ->schema([
                                        TextEntry::make('observacoes')
                                            ->label('Observações')
                                            ->markdown()
                                            ->placeholder('Sem observações.'),
                                    ])
                                    ->collapsible(),
                            ]),

                        Tabs\Tab::make('Anexos')
                            ->id('anexos')
                            ->schema([
                                Section::make('Documentos e imagens')
                                    ->schema([
                                        TextEntry::make('anexos')
                                            ->hiddenLabel()
                                            ->state(function ($record): HtmlString {
                                                $files = Collection::make($record->anexos ?? [])
                                                    ->filter(fn ($path) => filled($path))
                                                    ->values();

                                                if ($files->isEmpty()) {
                                                    return new HtmlString('<p class="text-sm text-gray-500">Nenhum anexo enviado.</p>');
                                                }

                                                $items = $files
                                                    ->map(function (string $path): string {
                                                        $name = e(basename($path));
                                                        $url = e(Storage::url($path));

                                                        return "<li><a class=\"text-primary-600 hover:text-primary-500\" href=\"{$url}\" target=\"_blank\" rel=\"noopener noreferrer\">{$name}</a></li>";
                                                    })
                                                    ->implode('');

                                                return new HtmlString("<ul class=\"list-disc pl-5 space-y-1\">{$items}</ul>");
                                            })
                                            ->html(),
                                    ])
                                    ->collapsible(),
                            ]),

                        Tabs\Tab::make('Histórico')
                            ->id('historico')
                            ->schema([
                                Section::make('Últimas ações')
                                    ->schema([
                                        IssuerControlLogEntry::make('log')
                                            ->hiddenLabel(),
                                    ])
                                    ->collapsible(),
                            ]),
                    ])
                    ->persistTab(),

            ]);
    }
}
