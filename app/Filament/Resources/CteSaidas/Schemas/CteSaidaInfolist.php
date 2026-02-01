<?php

namespace App\Filament\Resources\CteSaidas\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CteSaidaInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columns(3)
                    ->schema([
                        TextEntry::make('tomador_razao_social')
                            ->label('Tomador')
                            ->columnSpan(1)
                            ->weight('bold'),
                        TextEntry::make('tomador_cnpj')
                            ->label('CNPJ Tomador')
                            ->formatStateUsing(fn (?string $state): string => $state ? formatar_cnpj_cpf($state) : 'Não informado')
                            ->columnSpan(1),

                        Grid::make(3)
                            ->schema([
                                TextEntry::make('nCTe')
                                    ->label('CT-e Nº')
                                    ->weight('bold'),
                                TextEntry::make('serie')
                                    ->label('Série')
                                    ->weight('bold'),
                                TextEntry::make('tipo_tomador')
                                    ->label('Tipo Tomador')
                                    ->weight('bold'),
                                TextEntry::make('vCTe')
                                    ->label('Valor Total')
                                    ->money('BRL')
                                    ->weight('bold'),
                            ])->columnSpan(1),
                    ])
                    ->columnSpanFull(),

                Section::make('Documentos Referenciados')
                    ->schema([
                        TextEntry::make('nfe_chave')
                            ->label('Nota Fiscal Referenciada')
                            ->bulleted()
                            ->state(function ($record): array {
                                $state = $record->nfe_chave ?? null;
                                if ($state === null || $state === '') {
                                    return [];
                                }

                                if (is_string($state)) {
                                    $decoded = json_decode($state, true);
                                    if (json_last_error() === JSON_ERROR_NONE) {
                                        $state = $decoded;
                                    }
                                }

                                if (! is_array($state)) {
                                    return [];
                                }

                                $chaves = [];

                                foreach ($state as $item) {
                                    if (is_string($item) && trim($item) !== '') {
                                        $chaves[] = trim($item);

                                        continue;
                                    }

                                    if (is_array($item)) {
                                        $chave = $item['chave'] ?? null;
                                        if (is_string($chave) && trim($chave) !== '') {
                                            $chaves[] = trim($chave);
                                        }
                                    }
                                }

                                return $chaves;
                            })
                            ->placeholder('Não informado')
                            ->copyable()
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull()
                    ->collapsible(),

                Section::make('Detalhes do CT-e')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('nCTe')
                                    ->label('N°'),

                                TextEntry::make('emitente_razao_social')
                                    ->label('Razão Social Emitente'),

                                TextEntry::make('chave')
                                    ->label('Chave de Acesso')
                                    ->copyable(),

                                TextEntry::make('emitente_cnpj')
                                    ->label('CNPJ Emitente')
                                    ->formatStateUsing(fn (?string $state): string => $state ? formatar_cnpj_cpf($state) : 'Não informado'),

                                TextEntry::make('vCTe')
                                    ->label('Valor')
                                    ->money('BRL'),

                                TextEntry::make('emitente_logradouro')
                                    ->label('Endereço Emitente')
                                    ->default('Não informado'),

                                TextEntry::make('data_emissao')
                                    ->label('Data Emissão')
                                    ->dateTime('d/m/Y H:i'),

                                TextEntry::make('emitente_municipio_uf_cep')
                                    ->label('Município/UF - CEP')
                                    ->state(function ($record): string {
                                        $municipio = $record->emitente_municipio ?? '';
                                        $uf = $record->emitente_uf ?? '';
                                        $cep = $record->emitente_cep ?? '';

                                        if (empty($municipio) && empty($uf) && empty($cep)) {
                                            return 'Não informado';
                                        }

                                        $cepFormatado = ! empty($cep) ? ' - '.formatar_cep($cep) : '';

                                        return "{$municipio}/{$uf}{$cepFormatado}";
                                    }),
                            ]),
                    ])
                    ->columnSpanFull(),

                Section::make('Remetente')
                    ->schema([
                        Group::make()
                            ->schema([
                                TextEntry::make('remetente_razao_social')
                                    ->label('Razão Social'),
                                TextEntry::make('remetente_cnpj')
                                    ->label('CNPJ')
                                    ->formatStateUsing(fn (?string $state): string => $state ? formatar_cnpj_cpf($state) : 'Não informado'),
                                TextEntry::make('remetente_telefone')
                                    ->label('Telefone contato')
                                    ->default('Não informado'),
                                TextEntry::make('remetente_logradouro')
                                    ->label('Endereço')
                                    ->default('Não informado'),
                                TextEntry::make('remetente_municipio_uf_cep')
                                    ->label('Município/UF - CEP')
                                    ->state(function ($record): string {
                                        $municipio = $record->remetente_municipio ?? '';
                                        $uf = $record->remetente_uf ?? '';
                                        $cep = $record->remetente_cep ?? '';

                                        if (empty($municipio) && empty($uf) && empty($cep)) {
                                            return 'Não informado';
                                        }

                                        $cepFormatado = ! empty($cep) ? ' - '.formatar_cep($cep) : '';

                                        return "{$municipio}/{$uf}{$cepFormatado}";
                                    }),
                            ])->columns(2),
                    ])
                    ->columnSpanFull(),

                Section::make('Destinatário')
                    ->schema([
                        Group::make()
                            ->schema([
                                TextEntry::make('destinatario_razao_social')
                                    ->label('Razão Social'),
                                TextEntry::make('destinatario_cnpj')
                                    ->label('CNPJ')
                                    ->formatStateUsing(fn (?string $state): string => $state ? formatar_cnpj_cpf($state) : 'Não informado'),
                                TextEntry::make('destinatario_telefone')
                                    ->label('Telefone contato')
                                    ->default('Não informado'),
                                TextEntry::make('destinatario_logradouro')
                                    ->label('Endereço')
                                    ->default('Não informado'),
                                TextEntry::make('destinatario_municipio_uf_cep')
                                    ->label('Município/UF - CEP')
                                    ->state(function ($record): string {
                                        $municipio = $record->destinatario_municipio ?? '';
                                        $uf = $record->destinatario_uf ?? '';
                                        $cep = $record->destinatario_cep ?? '';

                                        if (empty($municipio) && empty($uf) && empty($cep)) {
                                            return 'Não informado';
                                        }

                                        $cepFormatado = ! empty($cep) ? ' - '.formatar_cep($cep) : '';

                                        return "{$municipio}/{$uf}{$cepFormatado}";
                                    }),
                            ])->columns(2),
                    ])
                    ->columnSpanFull(),

                Section::make('Dados do Expedidor')
                    ->schema([
                        Group::make()
                            ->schema([
                                TextEntry::make('expedidor_nome')
                                    ->label('Razão Social')
                                    ->default('Não informado'),

                                TextEntry::make('expedidor_cnpj')
                                    ->label('CNPJ')
                                    ->formatStateUsing(fn ($state) => $state ? formatar_cnpj_cpf($state) : 'Não informado'),

                                TextEntry::make('expedidor_ie')
                                    ->label('Inscrição Estadual')
                                    ->default('Não informado'),

                                TextEntry::make('expedidor_xFant')
                                    ->label('Nome Fantasia')
                                    ->default('Não informado'),

                                TextEntry::make('expedidor_telefone')
                                    ->label('Telefone')
                                    ->default('Não informado'),

                                TextEntry::make('expedidor_endereco')
                                    ->label('Endereço')
                                    ->state(function ($record): string {
                                        $logradouro = $record->expedidor_logradouro ?? '';
                                        $numero = $record->expedidor_numero ?? '';
                                        $complemento = $record->expedidor_complemento ? ', '.$record->expedidor_complemento : '';
                                        $bairro = $record->expedidor_bairro ? ', '.$record->expedidor_bairro : '';

                                        if (empty($logradouro) && empty($numero)) {
                                            return 'Não informado';
                                        }

                                        return "{$logradouro}, {$numero}{$complemento}{$bairro}";
                                    }),

                                TextEntry::make('municipio_uf_expedidor')
                                    ->label('Município/UF - CEP')
                                    ->state(function ($record): string {
                                        $municipio = $record->expedidor_municipio ?? '';
                                        $uf = $record->expedidor_uf ?? '';
                                        $cep = $record->expedidor_cep ?? '';

                                        if (empty($municipio) && empty($uf) && empty($cep)) {
                                            return 'Não informado';
                                        }

                                        $cepFormatado = ! empty($cep) ? ' - '.formatar_cep($cep) : '';

                                        return "{$municipio}/{$uf}{$cepFormatado}";
                                    }),
                            ])
                            ->columns(2),
                    ])
                    ->columnSpanFull(),

                Section::make('Dados do Recebedor')
                    ->schema([
                        Group::make()
                            ->schema([
                                TextEntry::make('recebedor_nome')
                                    ->label('Razão Social')
                                    ->default('Não informado'),

                                TextEntry::make('recebedor_cnpj')
                                    ->label('CNPJ')
                                    ->formatStateUsing(fn ($state) => $state ? formatar_cnpj_cpf($state) : 'Não informado'),

                                TextEntry::make('recebedor_ie')
                                    ->label('Inscrição Estadual')
                                    ->default('Não informado'),

                                TextEntry::make('recebedor_xFant')
                                    ->label('Nome Fantasia')
                                    ->default('Não informado'),

                                TextEntry::make('recebedor_telefone')
                                    ->label('Telefone')
                                    ->default('Não informado'),

                                TextEntry::make('recebedor_endereco')
                                    ->label('Endereço')
                                    ->state(function ($record): string {
                                        $logradouro = $record->recebedor_logradouro ?? '';
                                        $numero = $record->recebedor_numero ?? '';
                                        $complemento = $record->recebedor_complemento ? ', '.$record->recebedor_complemento : '';
                                        $bairro = $record->recebedor_bairro ? ', '.$record->recebedor_bairro : '';

                                        if (empty($logradouro) && empty($numero)) {
                                            return 'Não informado';
                                        }

                                        return "{$logradouro}, {$numero}{$complemento}{$bairro}";
                                    }),

                                TextEntry::make('recebedor_municipio_uf')
                                    ->label('Município/UF - CEP')
                                    ->state(function ($record): string {
                                        $municipio = $record->recebedor_municipio ?? '';
                                        $uf = $record->recebedor_uf ?? '';
                                        $cep = $record->recebedor_cep ?? '';

                                        if (empty($municipio) && empty($uf) && empty($cep)) {
                                            return 'Não informado';
                                        }

                                        $cepFormatado = ! empty($cep) ? ' - '.formatar_cep($cep) : '';

                                        return "{$municipio}/{$uf}{$cepFormatado}";
                                    }),
                            ])
                            ->columns(2),
                    ])
                    ->columnSpanFull(),

                Section::make('Impostos')
                    ->schema([
                        Group::make()
                            ->schema([
                                TextEntry::make('base_calculo_icms')
                                    ->label('Base Cálc. ICMS')
                                    ->money('BRL'),
                                TextEntry::make('valor_icms')
                                    ->label('Valor ICMS')
                                    ->money('BRL'),
                                TextEntry::make('aliquota_icms')
                                    ->label('Alíquota ICMS')
                                    ->state(fn ($record) => isset($record->aliquota_icms) ?
                                        number_format($record->aliquota_icms, 2, ',', '.').'%' : '0,00%'),
                                TextEntry::make('valor_servico')
                                    ->label('Valor Serviço')
                                    ->money('BRL'),
                                TextEntry::make('valor_receber')
                                    ->label('Valor a Receber')
                                    ->money('BRL'),
                            ])
                            ->columns(5),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
