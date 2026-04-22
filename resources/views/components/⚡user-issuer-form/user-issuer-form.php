<?php

use App\Models\Issuer;
use App\Models\User;
use Filament\Actions\AttachAction;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\DetachAction;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use STS\FilamentImpersonate\Actions\Impersonate;

new class extends Component implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions;
    use InteractsWithSchemas;
    use InteractsWithTable;

    public ?int $issuerId = null;

    public function mount(?int $issuerId = null)
    {
        $this->issuerId = $issuerId;
    }

    public function table(Table $table): Table
    {
        $issuer = Issuer::find($this->issuerId);

        return $table
            ->relationship(fn () => $issuer ? $issuer->users() : User::where('id', 0))
            ->headerActions([
                AttachAction::make()
                    ->recordTitleAttribute('name')
                    ->modalHeading('Vincular Usuário')
                    ->recordSelectOptionsQuery(
                        fn ($query) => $query
                            ->where('tenant_id', $issuer?->tenant_id)
                            ->whereDoesntHave('issuers', fn ($q) => $q->where('issuers.id', $this->issuerId))
                    ),
            ])
            ->columns([
                TextColumn::make('name')
                    ->label('Nome')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('E-mail')
                    ->searchable(),
                TextColumn::make('roles.name')
                    ->label('Grupos')
                    ->badge(),
            ])
            ->recordActions([
                DetachAction::make(),
                Impersonate::make()
                    ->hiddenLabel()
                    ->visible(function () {
                        return Auth::user()->hasRole('super-admin', 'admin', 'contabilidade');
                    })
                    ->tooltip('Entrar como usuário'),
            ]);
    }
};
