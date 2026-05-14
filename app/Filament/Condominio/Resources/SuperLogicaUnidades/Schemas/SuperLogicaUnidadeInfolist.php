<?php

namespace App\Filament\Condominio\Resources\SuperLogicaUnidades\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SuperLogicaUnidadeInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identificação')
                    ->columns(['default' => 2, 'sm' => 3])
                    ->schema([
                        TextEntry::make('id')
                            ->label('ID'),

                        TextEntry::make('id_condominio')
                            ->label('ID Condomínio'),

                        TextEntry::make('id_unidade_uni')
                            ->label('ID Unidade Superlógica'),
                    ])
                    ->columnSpanFull(),

                Section::make('Metadados')
                    ->columns(['default' => 2, 'sm' => 3])
                    ->collapsible()
                    ->schema(function ($record) {
                        $metadados = $record->metadados;

                        $entries = [];

                        if (isset($metadados['contatos']) && is_array($metadados['contatos'])) {
                            foreach ($metadados['contatos'] as $index => $contato) {
                                $icon = match ($contato['tipo'] ?? null) {
                                    '1' => 'heroicon-m-user',
                                    '2' => 'heroicon-m-building-office',
                                    default => 'heroicon-m-question-mark-circle',
                                };
                                $color = match ($contato['tipo'] ?? null) {
                                    '1' => 'info',
                                    '2' => 'warning',
                                    default => 'gray',
                                };

                                $entries[] = Section::make("Contato #{$index}")
                                    ->columns(['default' => 2, 'sm' => 3])
                                    ->schema([
                                        TextEntry::make("contatos.{$index}.st_nome_con")
                                            ->label('Nome')
                                            ->state($contato['st_nome_con'] ?? null),

                                        TextEntry::make("contatos.{$index}.st_email_con")
                                            ->label('E-mail')
                                            ->state($contato['st_email_con'] ?? null),

                                        TextEntry::make("contatos.{$index}.st_telefone_con")
                                            ->label('Telefone')
                                            ->state($contato['st_telefone_con'] ?? null),

                                        TextEntry::make("contatos.{$index}.st_celular_con")
                                            ->label('Celular')
                                            ->state($contato['st_celular_con'] ?? null),

                                        TextEntry::make("contatos.{$index}.st_cpf_con")
                                            ->label('CPF')
                                            ->state($contato['st_cpf_con'] ?? null),

                                        TextEntry::make("contatos.{$index}.st_cgc_con")
                                            ->label('CNPJ')
                                            ->state($contato['st_cgc_con'] ?? null),

                                        TextEntry::make("contatos.{$index}.st_rg_con")
                                            ->label('RG')
                                            ->state($contato['st_rg_con'] ?? null),

                                        TextEntry::make("contatos.{$index}.st_cep_con")
                                            ->label('CEP')
                                            ->state($contato['st_cep_con'] ?? null),

                                        TextEntry::make("contatos.{$index}.st_logradouro_con")
                                            ->label('Logradouro')
                                            ->state($contato['st_logradouro_con'] ?? null),

                                        TextEntry::make("contatos.{$index}.st_numero_con")
                                            ->label('Número')
                                            ->state($contato['st_numero_con'] ?? null),

                                        TextEntry::make("contatos.{$index}.st_complemento_con")
                                            ->label('Complemento')
                                            ->state($contato['st_complemento_con'] ?? null),

                                        TextEntry::make("contatos.{$index}.st_bairro_con")
                                            ->label('Bairro')
                                            ->state($contato['st_bairro_con'] ?? null),

                                        TextEntry::make("contatos.{$index}.st_cidade_con")
                                            ->label('Cidade')
                                            ->state($contato['st_cidade_con'] ?? null),

                                        TextEntry::make("contatos.{$index}.id_uf_uf")
                                            ->label('UF')
                                            ->state($contato['id_uf_uf'] ?? null),
                                    ])
                                    ->columnSpanFull();
                            }
                        }
                        if (isset($metadados['st_razao_soc']) && $metadados['st_razao_soc']) {
                            $entries[] = TextEntry::make('st_razao_soc')
                                ->label('Razão Social')
                                ->state($metadados['st_razao_soc']);
                        }

                        if (isset($metadados['st_nome_uni']) && $metadados['st_nome_uni']) {
                            $entries[] = TextEntry::make('st_nome_uni')
                                ->label('Nome da Unidade')
                                ->state($metadados['st_nome_uni']);
                        }

                        if (isset($metadados['st_tipo_uni']) && $metadados['st_tipo_uni']) {
                            $entries[] = TextEntry::make('st_tipo_uni')
                                ->label('Tipo de Unidade')
                                ->state($metadados['st_tipo_uni']);
                        }


                        return $entries;
                    })
                    ->columnSpanFull(),

                    Section::make('Auditoria')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('Criado em')
                            ->dateTime(),

                        TextEntry::make('updated_at')
                            ->label('Atualizado em')
                            ->dateTime(),
                    ])
                    ->columnSpanFull(),


            ]);
    }
}
