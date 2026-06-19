<?php

namespace App\Filament\Resources\NfeSaidas\Schemas;

use App\Filament\Infolists\Components\DifalEntry;
use App\Filament\Infolists\Components\ProductTableEntry;
use App\Models\NfeValidacaoTributaria;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;

class NfeSaidaInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Detalhes da Nota')
                    ->columnSpanFull()
                    ->tabs([
                        Tabs\Tab::make('Dados da Nota')
                            ->id('dados-gerais')
                            ->schema([
                                Section::make()
                                    ->columns(3)
                                    ->schema([
                                        TextEntry::make('emitente_razao_social')
                                            ->label('Emitente')
                                            ->columnSpan(2)
                                            ->weight('bold'),

                                        Grid::make(3)
                                            ->schema([
                                                TextEntry::make('nNF')
                                                    ->label('NF-e Nº')
                                                    ->weight('bold'),
                                                TextEntry::make('serie')
                                                    ->label('Série')
                                                    ->weight('bold'),
                                                TextEntry::make('vNfe')
                                                    ->label('Valor Total')
                                                    ->money('BRL')
                                                    ->weight('bold'),
                                            ]),
                                    ]),

                                Section::make('Etiquetas')
                                    ->hidden(fn ($record) => empty($record->tagNamesWithCodeAndValue()))
                                    ->schema([
                                        TextEntry::make('tags')
                                            ->hiddenLabel()
                                            ->live()
                                            ->state(fn ($record) => collect($record->tagNamesWithCodeAndValue())->map(fn ($tag) => "<li>{$tag}</li>")->implode(''))
                                            ->columnSpanFull()
                                            ->html(),
                                    ]),

                                Section::make('Detalhes da Nota')
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                TextEntry::make('nNF')
                                                    ->label('N°'),

                                                TextEntry::make('emitente_razao_social')
                                                    ->label('Razão Social Emitente'),

                                                TextEntry::make('chave')
                                                    ->label('Chave de Acesso')
                                                    ->copyable(),

                                                TextEntry::make('emitente_cnpj')
                                                    ->label('CNPJ Emitente')
                                                    ->formatStateUsing(fn (string $state): string => formatar_cnpj_cpf($state)),

                                                TextEntry::make('vNfe')
                                                    ->label('Valor')
                                                    ->money('BRL'),

                                                TextEntry::make('endereco_emitente_completo')
                                                    ->label('Endereço'),

                                                TextEntry::make('data_emissao')
                                                    ->label('Data Emissão')
                                                    ->dateTime('d/m/Y H:i'),

                                                TextEntry::make('nat_op')
                                                    ->label('Nat. Operação'),
                                            ]),
                                    ]),

                                Section::make('Destinatário')
                                    ->schema([
                                        Group::make()
                                            ->schema([
                                                TextEntry::make('destinatario_razao_social')
                                                    ->label('Razão Social'),
                                                TextEntry::make('destinatario_cnpj')
                                                    ->label('CNPJ')
                                                    ->formatStateUsing(fn (string $state): string => formatar_cnpj_cpf($state)),
                                                TextEntry::make('destinatario_fone')
                                                    ->label('Telefone contato'),
                                                TextEntry::make('endereco_destinatario_completo')
                                                    ->label('Endereço'),

                                            ])->columns(2),

                                        Section::make('Impostos')
                                            ->schema([
                                                Group::make()
                                                    ->schema([
                                                        TextEntry::make('vBC')
                                                            ->label('Base Cálc. ICMS')
                                                            ->money('BRL'),
                                                        TextEntry::make('vICMS')
                                                            ->label('Valor ICMS')
                                                            ->money('BRL'),
                                                        TextEntry::make('vBCST')
                                                            ->label('Base Calc. ST')
                                                            ->money('BRL'),
                                                        TextEntry::make('vST')
                                                            ->label('Valor ICMS ST')
                                                            ->money('BRL'),
                                                        TextEntry::make('vBCST')
                                                            ->label('V. Imp. Import.')
                                                            ->money('BRL'),
                                                        TextEntry::make('vBCST')
                                                            ->label('V. FCP UF Dest.')
                                                            ->money('BRL'),
                                                        TextEntry::make('vBCST')
                                                            ->label('V. ICMS UF Remet.')
                                                            ->money('BRL'),
                                                        TextEntry::make('vProd')
                                                            ->label('Total Produtos')
                                                            ->money('BRL'),

                                                    ])
                                                    ->columns(8),

                                                Group::make()
                                                    ->schema([
                                                        TextEntry::make('vFrete')
                                                            ->label('Valor Frete')
                                                            ->money('BRL'),
                                                        TextEntry::make('vSeg')
                                                            ->label('Valor Seguro')
                                                            ->money('BRL'),
                                                        TextEntry::make('vDesc')
                                                            ->label('Desconto')
                                                            ->money('BRL'),
                                                        TextEntry::make('vOutro')
                                                            ->label('Outras Despesas')
                                                            ->money('BRL'),

                                                        TextEntry::make('vIPI')
                                                            ->label('Valor IPI')
                                                            ->money('BRL'),
                                                        TextEntry::make('vBCST')
                                                            ->label('V. ICMS UF Dest.')
                                                            ->money('BRL'),
                                                        TextEntry::make('vCOFINS')
                                                            ->label('Valor COFINS')
                                                            ->money('BRL'),
                                                        TextEntry::make('vNfe')
                                                            ->label('Valor Total Nota')
                                                            ->money('BRL')
                                                            ->weight('bold'),
                                                    ])
                                                    ->columns(8),
                                            ]),

                                        Section::make('Produtos')
                                            ->schema([
                                                ProductTableEntry::make('product')
                                                    ->hiddenLabel(),

                                            ]),

                                    ]),

                            ]),
                        Tabs\Tab::make('Impostos Debitados')
                            ->id('impostos-debitados')
                            ->schema([
                                Section::make('Diferencial de Alíquota (DIFAL)')
                                    ->description('Cálculo do diferencial de alíquota entre estado de origem e destino. As alíquotas são obtidas automaticamente da tabela de ICMS interestadual do sistema.')
                                    ->schema([
                                        TextEntry::make('vICMSUFDest')
                                            ->label('Total DIFAL')
                                            ->money('BRL')
                                            ->visible(fn ($record) => $record->vICMSUFDest > 0)
                                            ->weight('bold'),
                                        DifalEntry::make('difals')
                                            ->hiddenLabel(),

                                    ]),
                            ]),
                        Tabs\Tab::make('Validação Tributária')
                            ->id('validacao-tributaria')
                            ->schema([
                                Section::make('Resultado da Validação')
                                    ->schema([
                                        TextEntry::make('validacao_acoes')
                                            ->hiddenLabel()
                                            ->html()
                                            ->state(function ($record): string {
                                                if (! $record) {
                                                    return '<p class="text-gray-500">Registro não disponível.</p>';
                                                }

                                                $validacoes = NfeValidacaoTributaria::porNfe($record->id)
                                                    ->where('issuer_id', currentIssuer()->id)
                                                    ->orderBy('created_at', 'desc')
                                                    ->get();

                                                $pendentes = $validacoes->where('status', 'pendente');
                                                $confirmados = $validacoes->where('status', 'confirmado');
                                                $ignorados = $validacoes->where('status', 'ignorado');
                                                $total = $validacoes->count();

                                                $html = '<div class="space-y-4">';

                                                $html .= '<div class="flex gap-4 mb-4">';
                                                $html .= '<div class="flex-1 p-4 rounded-lg border" style="background: #f0fdf4; border-color: #86efac;"><div class="text-2xl font-bold text-center text-green-600">'.$pendentes->count().'</div><div class="text-sm text-center text-gray-500">Pendentes</div></div>';
                                                $html .= '<div class="flex-1 p-4 rounded-lg border" style="background: #f8fafc; border-color: #cbd5e1;"><div class="text-2xl font-bold text-center text-gray-600">'.$confirmados->count().'</div><div class="text-sm text-center text-gray-500">Confirmados</div></div>';
                                                $html .= '<div class="flex-1 p-4 rounded-lg border" style="background: #f8fafc; border-color: #cbd5e1;"><div class="text-2xl font-bold text-center text-gray-600">'.$ignorados->count().'</div><div class="text-sm text-center text-gray-500">Ignorados</div></div>';
                                                $html .= '<div class="flex-1 p-4 rounded-lg border" style="background: #fffbeb; border-color: #fde68a;"><div class="text-2xl font-bold text-center text-amber-600">'.$total.'</div><div class="text-sm text-center text-gray-500">Total</div></div>';
                                                $html .= '</div>';

                                                if ($total === 0) {
                                                    $html .= '<div class="text-center py-8"><svg class="w-12 h-12 mx-auto text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>';
                                                    $html .= '<p class="mt-4 text-gray-500">Nenhuma validação foi executada para esta NF-e.</p>';
                                                    $html .= '<p class="text-gray-400 text-sm">Clique em "Validar Tributação" abaixo para iniciar.</p></div>';
                                                } else {
                                                    $html .= '<div class="space-y-2">';
                                                    foreach ($validacoes as $v) {
                                                        $leftColor = match ($v->severidade->value) {
                                                            'erro' => '#ef4444', 'aviso' => '#f59e0b', default => '#3b82f6'
                                                        };

                                                        $html .= '<div class="p-4 rounded-lg border" style="border-left: 4px solid '.$leftColor.';">';
                                                        $html .= '<div class="flex items-center gap-2 mb-2">';
                                                        $html .= '<span class="text-xs font-medium px-2 py-1 rounded text-white" style="background: '.$leftColor.';">'.mb_strtoupper($v->severidade->getLabel()).'</span>';
                                                        $html .= '<span class="text-sm font-medium">'.e($v->regra).'</span>';
                                                        if ($v->tipo_imposto) {
                                                            $html .= '<span class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded">'.e($v->tipo_imposto).'</span>';
                                                        }
                                                        if ($v->n_item) {
                                                            $html .= '<span class="text-xs text-gray-400">Item '.$v->n_item.'</span>';
                                                        }
                                                        $html .= '<span class="text-xs px-2 py-0.5 rounded text-white" style="background: '.match ($v->status->value) {
                                                            'pendente' => '#f59e0b', 'confirmado' => '#22c55e', default => '#6b7280'
                                                        }.';">'.$v->status->getLabel().'</span>';
                                                        $html .= '</div>';
                                                        $html .= '<p class="text-sm">'.e($v->mensagem).'</p>';
                                                        if ($v->valor_esperado || $v->valor_encontrado) {
                                                            $html .= '<div class="mt-1 flex gap-4 text-xs text-gray-500">';
                                                            if ($v->valor_esperado) {
                                                                $html .= '<span>Esperado: <strong>'.e($v->valor_esperado).'</strong></span>';
                                                            }
                                                            if ($v->valor_encontrado) {
                                                                $html .= '<span>Encontrado: <strong>'.e($v->valor_encontrado).'</strong></span>';
                                                            }
                                                            $html .= '</div>';
                                                        }
                                                        $html .= '</div>';
                                                    }
                                                    $html .= '</div>';
                                                }

                                                $html .= '</div>';

                                                return $html;
                                            })
                                            ->columnSpanFull(),
                                    ]),
                            ]),
                    ]),
            ]);
    }
}
