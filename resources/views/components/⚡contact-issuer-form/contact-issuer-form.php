<?php

use App\Enums\IssuerContactRoleEnum;
use App\Models\Issuer;
use App\Rules\CpfRule;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Livewire\Component;

new class extends Component implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions;
    use InteractsWithSchemas;
    use InteractsWithTable;

    public ?int $issuerId = null;

    public ?Issuer $issuer = null;

    public function mount(?int $issuerId = null)
    {
        $this->issuerId = $issuerId;
        $this->issuer = Issuer::find($this->issuerId);
    }

    public function table(Table $table): Table
    {
        $issuer = $this->issuer ?? Issuer::find($this->issuerId);

        return $table
            ->relationship(fn () => $issuer->contacts())
            ->headerActions([
                Action::make('add')
                    ->label('Adicionar Novo')
                    ->schema($this->getSchemaForm())
                    ->closeModalByClickingAway(false)
                    ->closeModalByEscaping(false)
                    ->action(function (array $data) {
                        $data['tenant_id'] = $this->issuer->tenant_id;
                        $data['issuer_id'] = $this->issuer->id;
                        $data['cpf'] = isset($data['cpf']) ? preg_replace('/\D/', '', $data['cpf']) : null;
                        $data['telefone_whatsapp'] = isset($data['telefone_whatsapp']) ? preg_replace('/\D/', '', $data['telefone_whatsapp']) : null;
                        $this->issuer->contacts()->create($data);
                    }),
            ])
            ->columns([
                TextColumn::make('nome')
                    ->label('Nome')
                    ->searchable(),
                TextColumn::make('funcao')
                    ->label('Função')
                    ->badge()
                    ->sortable(),
                TextColumn::make('cpf')
                    ->label('CPF')
                    ->formatStateUsing(fn ($state) => formatar_cnpj_cpf($state)),
                TextColumn::make('email')
                    ->label('E-mail'),
                TextColumn::make('telefone_whatsapp')
                    ->label('WhatsApp')
                    ->formatStateUsing(function ($state) {
                        $phone = preg_replace('/\D/', '', $state);
                        if (strlen($phone) === 11) {
                            return preg_replace('/(\d{2})(\d{5})(\d{4})/', '($1) $2-$3', $phone);
                        }
                        if (strlen($phone) === 10) {
                            return preg_replace('/(\d{2})(\d{4})(\d{4})/', '($1) $2-$3', $phone);
                        }

                        return $state;
                    }),
                TextColumn::make('unidade')
                    ->label('Unidade'),
                TextColumn::make('tipo_relacao')
                    ->label('Tipo de Relação')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'isencao' => 'Isenção',
                        'remuneracao' => 'Remuneração',
                        default => $state,
                    }),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Editar')
                    ->schema($this->getSchemaForm())
                    ->closeModalByClickingAway(false)
                    ->closeModalByEscaping(false)
                    ->action(function (array $data, $record) {
                        $data['cpf'] = isset($data['cpf']) ? preg_replace('/\D/', '', $data['cpf']) : null;
                        $data['telefone_whatsapp'] = isset($data['telefone_whatsapp']) ? preg_replace('/\D/', '', $data['telefone_whatsapp']) : null;
                        $record->update($data);
                    }),
                DeleteAction::make(),
            ]);
    }

    private function getSchemaForm(): array
    {
        return [
            Section::make()
                ->schema([
                    TextInput::make('nome')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('cpf')
                        ->label('CPF')
                        ->rules([new CpfRule])
                        ->mask('999.999.999-99')
                        ->placeholder('000.000.000-00'),
                    TextInput::make('email')
                        ->email()
                        ->maxLength(255),
                    TextInput::make('telefone_whatsapp')
                        ->label('Telefone/WhatsApp')
                        ->tel()
                        ->mask('(99) 99999-9999')
                        ->placeholder('(00) 00000-0000')
                        ->maxLength(20),
                    TextInput::make('unidade')
                        ->maxLength(255),
                    Select::make('funcao')
                        ->label('Função')
                        ->options(function () {
                            $issuer = currentIssuer();

                            if (! $issuer) {
                                return [];
                            }

                            return IssuerContactRoleEnum::getOptions($issuer->issuer_type);
                        })
                        ->required(),
                    Select::make('tipo_relacao')
                        ->label('Tipo de Relação')
                        ->options([
                            'isencao' => 'Isenção',
                            'remuneracao' => 'Remuneração',
                        ])
                        ->required(),
                ])
                ->columns(2)
                ->columnSpanFull(),
        ];
    }
};
