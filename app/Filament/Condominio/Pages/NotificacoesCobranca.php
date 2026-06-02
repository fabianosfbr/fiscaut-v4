<?php

namespace App\Filament\Condominio\Pages;

use App\Models\SuperLogicaCobrancaNotification;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use UnitEnum;

class NotificacoesCobranca extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament.condominio.pages.notificacoes-cobranca';

    protected static string|UnitEnum|null $navigationGroup = 'Cobranças';

    protected static ?string $title = 'Histórico de Notificações';

    protected static ?int $navigationSort = 3;

    public function table(Table $table): Table
    {
        return $table
            ->deferLoading()
            ->query(fn (): Builder => SuperLogicaCobrancaNotification::query()
                ->latest('sent_at'))
            ->columns([
                TextColumn::make('issuer.razao_social')
                    ->label('Razão Social')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('dados_unidade')
                    ->label('Bloco/Unidade')
                    ->state(function ($record) {
                        $bloco = $record->unidade->metadados['st_bloco_uni'] ?? '-';
                        $unidade = $record->unidade->metadados['st_unidade_uni'] ?? ' ';

                        return 'Bloco: '.$bloco.' Unidade: '.$unidade;
                    }),
                TextColumn::make('dados_contato')
                    ->label('Email')
                    ->state(function ($record) {
                        return $record->data['recipient_email'] ?? '-';
                    })
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->where('data->recipient_email', 'like', "%{$search}%");
                    })
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->orderBy('data->recipient_email', $direction);
                    }),
                TextColumn::make('sent_at')
                    ->label('Enviado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->state(function ($record) {
                        return $record->status == 'sent' ? 'Entregue' : 'Pendente';
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'success' => 'success',
                        'error' => 'danger',
                        'pending' => 'warning',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('error_message')
                    ->label('Erro')
                    ->limit(50)
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
            ])
            ->filters([
                Filter::make('email')
                    ->label('Email')
                    ->form([
                        TextInput::make('email')
                            ->label('Email')
                            ->placeholder('Digite o email...'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (! filled($data['email'] ?? null)) {
                            return $query;
                        }

                        return $query->where('data->recipient_email', 'like', "%{$data['email']}%");
                    })
                    ->indicateUsing(function (array $data): ?Indicator {
                        if (! filled($data['email'] ?? null)) {
                            return null;
                        }

                        return Indicator::make('Email: '.$data['email'])
                            ->removeField('email');
                    }),

                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'sent' => 'Entregue',
                        'pending' => 'Pendente',
                        'error' => 'Erro',
                    ]),

                Filter::make('sent_at')
                    ->label('Data de Envio')
                    ->columnSpan(2)
                    ->form([
                        DatePicker::make('sent_at_de')
                            ->label('Enviado a partir de'),
                        DatePicker::make('sent_at_ate')
                            ->label('Enviado até'),
                    ])
                    ->columns(2)
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['sent_at_de'] ?? null) {
                            $indicators[] = Indicator::make('Enviado a partir de '.Carbon::parse($data['sent_at_de'])->format('d/m/Y'))
                                ->removeField('sent_at_de');
                        }
                        if ($data['sent_at_ate'] ?? null) {
                            $indicators[] = Indicator::make('Enviado até '.Carbon::parse($data['sent_at_ate'])->format('d/m/Y'))
                                ->removeField('sent_at_ate');
                        }

                        return $indicators;
                    })
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['sent_at_de'] ?? null,
                                fn (Builder $query, $date): Builder => $query->whereDate('sent_at', '>=', $date),
                            )
                            ->when(
                                $data['sent_at_ate'] ?? null,
                                fn (Builder $query, $date): Builder => $query->whereDate('sent_at', '<=', $date),
                            );
                    }),
            ])
            ->filtersFormColumns(4)
            ->persistFiltersInSession()
            ->deferFilters(true)
            ->defaultSort('sent_at', 'desc');
    }
}
