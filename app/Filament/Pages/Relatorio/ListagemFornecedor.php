<?php

namespace App\Filament\Pages\Relatorio;

use UnitEnum;
use Filament\Pages\Page;
use Filament\Tables\Table;
use App\Models\NotaFiscalEletronica;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Schemas\Concerns\InteractsWithSchemas;

class ListagemFornecedor extends Page implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions;
    use InteractsWithSchemas;
    use InteractsWithTable;

    protected static ?string $navigationLabel = 'Listagem de Fornecedores';

    protected static ?string $title = 'Listagem de Fornecedores';

    protected static ?string $slug = 'listagem-fornecedores';

    protected static string|UnitEnum|null $navigationGroup = 'Relatórios';

    protected string $view = 'filament.pages.relatorio.listagem-fornecedor';

    public function table(Table $table): Table
    {
        return $table
            ->recordUrl(null)
            ->query(function () {
                $issuer = Auth::user()->currentIssuer;
                $baseQuery = NotaFiscalEletronica::query()
                    ->selectRaw('MIN(id) as id')
                    ->selectRaw('SUM(vNfe) as total')
                    ->addSelect('emitente_cnpj', 'emitente_razao_social')
                    ->where('status_nota', 100)
                    ->where('destinatario_cnpj', $issuer->cnpj)
                    ->groupBy('emitente_cnpj', 'emitente_razao_social');

                return NotaFiscalEletronica::query()
                    ->fromSub($baseQuery, 'nfes')
                    ->orderByDesc('total');
            })
            ->columns([
                 TextColumn::make('emitente_razao_social')
                    ->label('Razão Social')
                    ->searchable(),
                TextColumn::make('emitente_cnpj')
                    ->label('CNPJ')
                    ->searchable()
                    ->copyable(),
                TextColumn::make('total')
                    ->label('Valor Total')
                    ->money('BRL'),
            ])
            ->filters([
                // ...
            ])
            ->recordActions([
                // ...
            ])
            ->toolbarActions([
                // ...
            ]);
    }
}
