<?php

use App\Models\SuperLogicaCobrancaNotification;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Component;

new class extends Component implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions;
    use InteractsWithSchemas;
    use InteractsWithTable;

    public array $recebimentos = [];

    public function mount(array $recebimentos)
    {
        $this->recebimentos = $recebimentos;
    }

    public function table(Table $table): Table
    {
        return $table

            ->records(function (int $page, int $recordsPerPage): LengthAwarePaginator {
                // dd($this->recebimentos);
                $notificacoes = SuperLogicaCobrancaNotification::where('id_unidade_uni', $this->recebimentos['id_unidade_uni'])->orderBy('created_at', 'desc')
                    ->get()->toArray();
                $records = collect($notificacoes)->forPage($page, $recordsPerPage);

                return new LengthAwarePaginator(
                    $records,
                    total: count($notificacoes), // Total number of records across all pages
                    perPage: $recordsPerPage,
                    currentPage: $page,
                );
            })
            ->columns([
                TextColumn::make('sent_at')
                    ->label('Data de Envio')
                    ->formatStateUsing(fn ($state) => \Carbon\Carbon::parse($state)->format('d/m/Y H:i:s')),
                TextColumn::make('data.recipient_email')
                    ->label('Enviada para'),

            ]);
    }
};
