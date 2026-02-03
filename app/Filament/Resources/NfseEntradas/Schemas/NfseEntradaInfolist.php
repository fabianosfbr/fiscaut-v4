<?php

namespace App\Filament\Resources\NfseEntradas\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;

class NfseEntradaInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Detalhes da NFSe')
                    ->columnSpanFull()
                    ->tabs([
                        Tabs\Tab::make('DANFE')
                            ->id('danfe')
                            ->schema([
                                Section::make('Nota Fiscal de Serviço Eletrônica (NFS-e)')
                                    ->columns(3)
                                    ->schema([
                                        TextEntry::make('prestador_servico')
                                            ->label('Prestador de Serviços')
                                            ->columnSpan(2)
                                            ->weight('bold')
                                            ->placeholder('Não informado'),

                                        Grid::make(2)
                                            ->schema([
                                                TextEntry::make('numero')
                                                    ->label('NFS-e Nº')
                                                    ->weight('bold')
                                                    ->placeholder('Não informado'),

                                                TextEntry::make('data_emissao')
                                                    ->label('Emissão')
                                                    ->dateTime('d/m/Y H:i')
                                                    ->weight('bold')
                                                    ->placeholder('Não informado'),

                                                TextEntry::make('codigo_verificacao')
                                                    ->label('Código de Verificação')
                                                    ->copyable()
                                                    ->placeholder('Não informado'),

                                                TextEntry::make('cancelada')
                                                    ->label('Status')
                                                    ->state(fn ($record) => $record->cancelada ? 'Cancelada' : 'Ativa')
                                                    ->badge(),
                                            ])
                                            ->columnSpan(1),
                                    ])
                                    ->columnSpanFull(),

                                Section::make('Identificação')
                                    ->schema([
                                        Grid::make(6)
                                            ->schema([
                                                TextEntry::make('chave')
                                                    ->label('Chave de Acesso')
                                                    ->copyable()
                                                    ->columnSpan(3)
                                                    ->placeholder('Não informado'),

                                                TextEntry::make('codigo_municipio')
                                                    ->label('Código do Município')
                                                    ->columnSpan(1)
                                                    ->placeholder('Não informado'),

                                                TextEntry::make('qr_code')
                                                    ->label('QR Code')
                                                    ->copyable()
                                                    ->columnSpan(2)
                                                    ->placeholder('Não informado'),
                                            ]),
                                    ])
                                    ->columnSpanFull(),

                                Section::make('Dados Complementares')
                                    ->schema([
                                        Grid::make(4)
                                            ->schema([
                                                TextEntry::make('nfse_competencia')
                                                    ->label('Competência')
                                                    ->state(fn ($record) => self::nfseStringFromRecord(
                                                        $record,
                                                        'infNFSe.DPS.infDPS.dCompet',
                                                        'infNFSe.DPS.infDPS.ide.dCompet',
                                                        'infNFSe.ide.dCompet',
                                                        'infNFSe.dCompet',
                                                    ))
                                                    ->dateTime('d/m/Y')
                                                    ->placeholder('Não informado'),

                                                TextEntry::make('nfse_natureza_operacao')
                                                    ->label('Natureza da Operação')
                                                    ->state(fn ($record) => self::nfseStringFromRecord(
                                                        $record,
                                                        'infNFSe.DPS.infDPS.natOp',
                                                        'infNFSe.DPS.infDPS.ide.natOp',
                                                        'infNFSe.ide.natOp',
                                                        'infNFSe.natOp',
                                                    ))
                                                    ->placeholder('Não informado'),

                                                TextEntry::make('nfse_municipio_incidencia')
                                                    ->label('Município de Incidência')
                                                    ->state(fn ($record) => self::nfseStringFromRecord(
                                                        $record,
                                                        'infNFSe.xLocIncid',
                                                        'infNFSe.cLocIncid',
                                                        'infNFSe.xLocPrestacao',
                                                        'infNFSe.cLocPrestacao',
                                                    ))
                                                    ->placeholder('Não informado'),

                                                TextEntry::make('codigo_municipio')
                                                    ->label('Código do Município')
                                                    ->placeholder('Não informado'),
                                            ]),
                                    ])
                                    ->columnSpanFull()
                                    ->collapsible(),

                                Section::make('Prestador de Serviços')
                                    ->schema([
                                        Group::make()
                                            ->schema([
                                                TextEntry::make('prestador_servico')
                                                    ->label('Razão Social / Nome')
                                                    ->placeholder('Não informado'),

                                                TextEntry::make('prestador_cnpj')
                                                    ->label('CNPJ/CPF')
                                                    ->formatStateUsing(fn (?string $state): string => $state ? formatar_cnpj_cpf($state) : 'Não informado'),

                                                TextEntry::make('prestador_im')
                                                    ->label('Inscrição Municipal (IM)')
                                                    ->placeholder('Não informado'),

                                                TextEntry::make('prestador_endereco')
                                                    ->label('Endereço')
                                                    ->state(function ($record): ?string {
                                                        $root = self::nfseRootFromRecord($record);

                                                        return self::nfseEnderecoFromRoot(
                                                            $root,
                                                            'infNFSe.emit.enderNac',
                                                            'infNFSe.emit.ender',
                                                            'infNFSe.emit',
                                                            'infNFSe.DPS.infDPS.prest',
                                                            'infNFSe.DPS.infDPS.prestador',
                                                            'infNFSe.prest',
                                                            'infNFSe.prestador',
                                                            'infNFSe.infPrestador',
                                                            'infNFSe.prestadorServico',
                                                        );
                                                    })
                                                    ->placeholder('Não informado'),
                                            ])
                                            ->columns(2),
                                    ])
                                    ->columnSpanFull(),

                                Section::make('Tomador de Serviços')
                                    ->schema([
                                        Group::make()
                                            ->schema([
                                                TextEntry::make('tomador_servico')
                                                    ->label('Razão Social / Nome')
                                                    ->placeholder('Não informado'),

                                                TextEntry::make('tomador_cnpj')
                                                    ->label('CNPJ/CPF')
                                                    ->formatStateUsing(fn (?string $state): string => $state ? formatar_cnpj_cpf($state) : 'Não informado'),

                                                TextEntry::make('tomador_im')
                                                    ->label('Inscrição Municipal (IM)')
                                                    ->placeholder('Não informado'),

                                                TextEntry::make('tomador_endereco')
                                                    ->label('Endereço')
                                                    ->state(function ($record): ?string {
                                                        $root = self::nfseRootFromRecord($record);

                                                        return self::nfseEnderecoFromRoot(
                                                            $root,
                                                            'infNFSe.DPS.infDPS.toma',
                                                            'infNFSe.DPS.infDPS.tomador',
                                                            'infNFSe.toma',
                                                            'infNFSe.tomador',
                                                            'infNFSe.infTomador',
                                                            'infNFSe.tomadorServico',
                                                        );
                                                    })
                                                    ->placeholder('Não informado'),
                                            ])
                                            ->columns(2),
                                    ])
                                    ->columnSpanFull(),

                                Section::make('Serviço')
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                TextEntry::make('nfse_codigo_servico_extraido')
                                                    ->label('Código do Serviço')
                                                    ->placeholder('Não informado'),

                                                TextEntry::make('nfse_descricao_servico_extraida')
                                                    ->label('Descrição do Serviço')
                                                    ->placeholder('Não informado'),
                                            ]),

                                        TextEntry::make('nfse_discriminacao_extraida')
                                            ->label('Discriminação do Serviço')
                                            ->state(function ($record): ?string {
                                                $value = $record->nfse_discriminacao_extraida ?? null;
                                                if (! is_string($value) || trim($value) === '') {
                                                    return null;
                                                }

                                                return '<pre style="white-space: pre-wrap; word-break: break-word;">'.e($value).'</pre>';
                                            })
                                            ->html()
                                            ->placeholder('Não informado')
                                            ->columnSpanFull(),
                                    ])
                                    ->columnSpanFull(),

                                Section::make('Valores / Tributação (ISS)')
                                    ->schema([
                                        Group::make()
                                            ->schema([
                                                TextEntry::make('valor_servico')
                                                    ->label('Valor Líquido (vLiq)')
                                                    ->money('BRL')
                                                    ->weight('bold')
                                                    ->placeholder('Não informado'),

                                                TextEntry::make('nfse_valores_vserv')
                                                    ->label('Valor Serviços (vServ)')
                                                    ->state(fn ($record) => self::nfseValoresNumericFromRecord($record, 'vServ', 'vServicos', 'vServPrest.vServ'))
                                                    ->money('BRL')
                                                    ->placeholder('Não informado'),

                                                TextEntry::make('nfse_valores_vdedu')
                                                    ->label('Deduções (vDedu)')
                                                    ->state(fn ($record) => self::nfseValoresNumericFromRecord($record, 'vDedu', 'vDeducoes'))
                                                    ->money('BRL')
                                                    ->placeholder('Não informado'),

                                                TextEntry::make('nfse_valores_vdesccond')
                                                    ->label('Desc. Cond. (vDescCond)')
                                                    ->state(fn ($record) => self::nfseValoresNumericFromRecord($record, 'vDescCond', 'vDescCondicionado'))
                                                    ->money('BRL')
                                                    ->placeholder('Não informado'),

                                                TextEntry::make('nfse_valores_vdescincond')
                                                    ->label('Desc. Incond. (vDescIncond)')
                                                    ->state(fn ($record) => self::nfseValoresNumericFromRecord($record, 'vDescIncond', 'vDescIncondicionado'))
                                                    ->money('BRL')
                                                    ->placeholder('Não informado'),

                                                TextEntry::make('nfse_base_calculo_iss_extraida')
                                                    ->label('Base de Cálculo (vBC)')
                                                    ->money('BRL')
                                                    ->placeholder('Não informado'),

                                                TextEntry::make('nfse_aliquota_iss_extraida')
                                                    ->label('Alíquota (pAliq)')
                                                    ->state(fn ($record) => isset($record->nfse_aliquota_iss_extraida)
                                                        ? number_format((float) $record->nfse_aliquota_iss_extraida, 2, ',', '.').'%'
                                                        : null)
                                                    ->placeholder('Não informado'),

                                                TextEntry::make('nfse_valor_iss_extraido')
                                                    ->label('ISS (vISS)')
                                                    ->money('BRL')
                                                    ->placeholder('Não informado'),

                                                TextEntry::make('nfse_iss_retido_extraido')
                                                    ->label('ISS Retido (vISSRet)')
                                                    ->money('BRL')
                                                    ->placeholder('Não informado'),

                                                TextEntry::make('nfse_valores_voutros')
                                                    ->label('Outras Retenções (vOutros)')
                                                    ->state(fn ($record) => self::nfseValoresNumericFromRecord($record, 'vOutros', 'vOthRet'))
                                                    ->money('BRL')
                                                    ->placeholder('Não informado'),
                                            ])
                                            ->columns(5),
                                    ])
                                    ->columnSpanFull(),

                                Section::make('Informações / Controle')
                                    ->schema([
                                        Grid::make(6)
                                            ->schema([
                                                TextEntry::make('data_entrada')
                                                    ->label('Data de Entrada')
                                                    ->date('d/m/Y')
                                                    ->columnSpan(2)
                                                    ->placeholder('Não informado'),

                                                TextEntry::make('apurada.status')
                                                    ->label('Apurada')
                                                    ->state(fn ($record) => $record->apurada?->status ? 'Sim' : 'Não')
                                                    ->badge()
                                                    ->columnSpan(1),

                                                TextEntry::make('origem')
                                                    ->label('Origem')
                                                    ->columnSpan(1)
                                                    ->placeholder('Não informado'),

                                            ]),
                                    ])
                                    ->columnSpanFull(),

                                Section::make('Cancelamento')
                                    ->schema([
                                        Grid::make(3)
                                            ->schema([
                                                TextEntry::make('data_cancelamento')
                                                    ->label('Data de Cancelamento')
                                                    ->date('d/m/Y')
                                                    ->placeholder('Não informado'),

                                                TextEntry::make('motivo_cancelamento')
                                                    ->label('Motivo do Cancelamento')
                                                    ->columnSpan(2)
                                                    ->placeholder('Não informado'),
                                            ]),
                                    ])
                                    ->columnSpanFull()
                                    ->collapsible(),
                            ]),

                        Tabs\Tab::make('XML')
                            ->id('xml')
                            ->schema([
                                Section::make('XML')
                                    ->schema([
                                        TextEntry::make('xml_extraido')
                                            ->hiddenLabel()
                                            ->copyable()
                                            ->state(function ($record): ?string {
                                                $xml = $record->xml_extraido ?? null;

                                                if (! is_string($xml) || trim($xml) === '') {
                                                    return null;
                                                }

                                                return '<pre style="white-space: pre-wrap; word-break: break-word;">'.prettyPrintXmlToBrowser($xml).'</pre>';
                                            })
                                            ->html()
                                            ->placeholder('Não informado')
                                            ->copyable()
                                            ->columnSpanFull(),
                                    ])
                                    ->columnSpanFull()
                                    ->collapsible(),

                            ]),
                    ]),
            ]);
    }

    private static function nfseRootFromRecord($record): ?array
    {
        $json = $record->nfse_xml_json ?? null;
        if (! is_string($json) || trim($json) === '') {
            return null;
        }

        $data = json_decode($json, true);
        if (! is_array($data) || $data === []) {
            return null;
        }

        $rootKey = array_key_first($data);
        if (! is_string($rootKey) || $rootKey === '') {
            return null;
        }

        $root = $data[$rootKey] ?? null;

        return is_array($root) && $root !== [] ? $root : null;
    }

    private static function nfseStringFromRecord($record, string ...$paths): ?string
    {
        $root = self::nfseRootFromRecord($record);
        if (! is_array($root)) {
            return null;
        }

        return self::firstNonEmptyStringFromArray($root, ...$paths);
    }

    private static function nfseValoresFromRecord($record): ?array
    {
        $json = $record->nfse_valores_xml ?? null;
        if (is_string($json) && trim($json) !== '') {
            $valores = json_decode($json, true);
            if (is_array($valores) && $valores !== []) {
                return $valores;
            }
        }

        $root = self::nfseRootFromRecord($record);
        $valoresInfNfse = is_array($root) ? data_get($root, 'infNFSe.valores') : null;
        $valoresInfDps = is_array($root) ? data_get($root, 'infNFSe.DPS.infDPS.valores') : null;

        if (is_array($valoresInfNfse) && $valoresInfNfse !== [] && is_array($valoresInfDps) && $valoresInfDps !== []) {
            $merged = array_replace_recursive($valoresInfNfse, $valoresInfDps);

            return $merged !== [] ? $merged : null;
        }

        $valores = is_array($valoresInfDps) && $valoresInfDps !== []
            ? $valoresInfDps
            : $valoresInfNfse;

        return is_array($valores) && $valores !== [] ? $valores : null;
    }

    private static function nfseValoresNumericFromRecord($record, string ...$keys): ?float
    {
        $valores = self::nfseValoresFromRecord($record);
        if (! is_array($valores)) {
            return null;
        }

        foreach ($keys as $key) {
            $value = data_get($valores, $key);
            if (is_numeric($value)) {
                return (float) $value;
            }
        }

        return null;
    }

    private static function nfseEnderecoFromRoot(?array $root, string ...$nodePaths): ?string
    {
        if (! is_array($root)) {
            return null;
        }

        $node = null;
        foreach ($nodePaths as $path) {
            $value = data_get($root, $path);
            if (is_array($value) && $value !== []) {
                $node = $value;
                break;
            }
        }

        if (! is_array($node)) {
            return null;
        }

        $endereco = null;
        foreach (['end', 'ender', 'endereco', 'enderNac', 'endNac', 'enderecoNacional'] as $path) {
            $value = data_get($node, $path);
            if (is_array($value) && $value !== []) {
                $endereco = $value;
                break;
            }
        }

        $data = is_array($endereco) ? $endereco : $node;

        $logradouro = self::firstNonEmptyStringFromArray($data, 'xLgr', 'xLogr', 'xLog', 'logradouro');
        $numero = self::firstNonEmptyStringFromArray($data, 'nro', 'nroLog', 'numero');
        $bairro = self::firstNonEmptyStringFromArray($data, 'xBairro', 'bairro');
        $municipio = self::firstNonEmptyStringFromArray($data, 'xMun', 'municipio');
        $uf = self::firstNonEmptyStringFromArray($data, 'UF', 'uf');
        $cep = self::firstNonEmptyStringFromArray($data, 'CEP', 'cep');

        $linha1Parts = array_values(array_filter([$logradouro, $numero], fn ($v) => is_string($v) && trim($v) !== ''));
        $linha1 = $linha1Parts !== [] ? implode(' ', $linha1Parts) : null;

        $cidadeUfParts = array_values(array_filter([$municipio, $uf], fn ($v) => is_string($v) && trim($v) !== ''));
        $cidadeUf = $cidadeUfParts !== [] ? implode('/', $cidadeUfParts) : null;

        $linha2Parts = array_values(array_filter([$bairro, $cidadeUf], fn ($v) => is_string($v) && trim($v) !== ''));
        $linha2 = $linha2Parts !== [] ? implode(' - ', $linha2Parts) : null;

        $partes = array_values(array_filter([$linha1, $linha2, $cep ? "CEP {$cep}" : null], fn ($v) => is_string($v) && trim($v) !== ''));

        return $partes !== [] ? implode(' | ', $partes) : null;
    }

    private static function firstNonEmptyStringFromArray(array $data, string ...$keys): ?string
    {
        foreach ($keys as $key) {
            $value = data_get($data, $key);
            if (! is_string($value)) {
                continue;
            }

            $value = trim($value);
            if ($value !== '') {
                return $value;
            }
        }

        return null;
    }
}
