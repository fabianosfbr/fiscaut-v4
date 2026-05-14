<?php

namespace App\Filament\Condominio\Pages;

use Filament\Actions\Action;
use Filament\Infolists\Components\TextEntry;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Cache;

class DetalhesCobranca extends Page implements HasSchemas
{
    use InteractsWithSchemas;

    protected string $view = 'filament.condominio.pages.detalhes-cobranca';

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $title = 'Detalhes da Cobrança';

    public ?array $record = null;

    public ?array $unidade = null;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('voltar')
                ->label('Voltar')
                ->url(fn(): string => route('filament.condominio.pages.inadimplencia'))
                ->color('gray'),
        ];
    }

    public function mount(): void
    {
        $this->record = $this->resolveRecord();

        ds($this->record);

        $this->unidade = $this->resolveUnidade();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Detalhes da Unidade')
                    ->schema([
                        TextEntry::make('id_condominio_cond')
                            ->state(function () {
                                return $this->record['id_condominio_cond'];
                            })
                            ->label('Condominio'),
                        TextEntry::make('id_unidade_uni')
                            ->state(function () {
                                return $this->record['id_unidade_uni'];
                            })
                            ->label('Unidade'),
                        TextEntry::make('unidade.unidade')
                            ->state(function () {
                                return $this->unidade['unidade'];
                            })
                            ->label('Unidade'),
                        TextEntry::make('unidade.bloco')
                            ->state(function () {
                                return $this->unidade['bloco'];
                            })
                            ->label('Bloco'),
                        TextEntry::make('unidade.nome')
                            ->state(function () {
                                return $this->unidade['nome'];
                            })
                            ->label('Sacado'),
                        TextEntry::make('cpf')
                            ->state(function () {
                                return $this->unidade['cpf'] ?? '-';
                            })
                            ->label('CPF'),
                        TextEntry::make('email')
                            ->state(function () {
                                return $this->unidade['email'] ?? '-';
                            })
                            ->label('Email'),
                        TextEntry::make('celular')
                            ->state(function () {
                                return filled($this->unidade['celular']) ? $this->unidade['celular'] : '-';
                            })
                            ->label('Celular'),

                    ])->columns(6)
                    ->columnSpanFull(),

            ]);
    }

    protected function resolveRecord(): ?array
    {
        $recordKey = request()->query('record_key');

        if ($recordKey) {
            $cachedRecord = Cache::get($recordKey);
            if (is_array($cachedRecord)) {
                return $cachedRecord;
            }
        }

        // Suporte para o formato antigo em base64 (caso haja links antigos)
        $encoded = request()->query('record');

        if (! $encoded) {
            return null;
        }

        try {
            $decoded = base64_decode($encoded);
            $record = json_decode($decoded, true);

            return is_array($record) ? $record : null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    protected function resolveUnidade(): ?array
    {

        $values = [];

        $values = [
            'id' => data_get($this->record, 'id_unidade_uni') ?? data_get($this->record, 'st_unidade_uni'),
            'bloco' => $st_bloco_uni = data_get($this->record, 'st_bloco_uni') ?? null,
            'unidade' => $st_bloco_uni = data_get($this->record, 'st_unidade_uni') ?? null,
            'nome' => data_get($this->record, 'recebimento.0.contatosunidade.0.proprietario.0.nome'),
            'cpf' => data_get($this->record, 'recebimento.0.contatosunidade.0.proprietario.0.cpf'),
            'email' => data_get($this->record, 'recebimento.0.contatosunidade.0.proprietario.0.email'),
            'telefone' => data_get($this->record, 'recebimento.0.contatosunidade.0.proprietario.0.fone'),
            'celular' => data_get($this->record, 'recebimento.0.contatosunidade.0.proprietario.0.fax'),
        ];

        return $values;
    }
}
