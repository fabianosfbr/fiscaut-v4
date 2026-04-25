<?php

namespace App\Filament\Condominio\Resources\IssuerAssembleias;

use App\Enums\IssuerAgeTypeEnum;
use App\Filament\Condominio\Resources\IssuerAssembleias\Pages\CreateIssuerAssembleia;
use App\Filament\Condominio\Resources\IssuerAssembleias\Pages\EditIssuerAssembleia;
use App\Filament\Condominio\Resources\IssuerAssembleias\Pages\ListIssuerAssembleias;
use App\Filament\Condominio\Resources\IssuerAssembleias\Schemas\IssuerAssembleiaForm;
use App\Filament\Condominio\Resources\IssuerAssembleias\Tables\IssuerAssembleiasTable;
use App\Models\IssuerAssembleia;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class IssuerAssembleiaResource extends Resource
{
    protected static ?string $model = IssuerAssembleia::class;

    protected static ?string $modelLabel = 'AGO/AGE';

    protected static ?string $pluralModelLabel = 'AGOs/AGEs';

    protected static string|UnitEnum|null $navigationGroup = 'Assembleias';

    public static function form(Schema $schema): Schema
    {
        return IssuerAssembleiaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return IssuerAssembleiasTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListIssuerAssembleias::route('/'),
            'create' => CreateIssuerAssembleia::route('/create'),
            'edit' => EditIssuerAssembleia::route('/{record}/edit'),
        ];
    }

    public static function cleanData(array $data): array
    {
        $type = $data['type'] ?? null;

        if (! $type) {
            return $data;
        }

        if ($type instanceof IssuerAgeTypeEnum) {
            $type = $type->value;
        }

        if ($type === IssuerAgeTypeEnum::AGE->value) {
            // Clear AGO fields
            $data['data_limite_ago'] = null;
            $data['prazo_tecnico_edital'] = null;
            $data['mandato_fim'] = null;
            $data['prazo_tecnico_mandato'] = null;
            $data['mandato_conselho_fim'] = null;
            $data['prazo_tecnico_mandato_conselho'] = null;
            $data['mandato_banco_fim'] = null;
            $data['prazo_tecnico_mandato_banco'] = null;
            $data['boleto_dia_vencimento'] = null;
            $data['boleto_tipo_prazo'] = null;
            $data['boleto_gerado_por'] = null;
            $data['boleto_forma_rateio'] = null;
            $data['tem_isencao_remuneracao'] = null;
            $data['tem_isencao'] = false;
            $data['tem_remuneracao'] = false;
            $data['quem_recebe_isencao'] = null;
            $data['quem_recebe_remuneracao'] = null;
            $data['valor_isencao'] = null;
            $data['valor_remuneracao'] = null;
        } elseif ($type === IssuerAgeTypeEnum::AGO->value) {
            // Clear AGE fields
            $data['vigencia_date'] = null;
            $data['prazo_tecnico'] = null;

            // Clear exemption/remuneration fields if not selected
            $isencoesRemuneracoes = $data['tem_isencao_remuneracao'] ?? [];
            if (! is_array($isencoesRemuneracoes)) {
                $isencoesRemuneracoes = [];
            }

            if (! in_array('isencao', $isencoesRemuneracoes)) {
                $data['quem_recebe_isencao'] = null;
                $data['valor_isencao'] = null;
            }

            if (! in_array('remuneracao', $isencoesRemuneracoes)) {
                $data['quem_recebe_remuneracao'] = null;
                $data['valor_remuneracao'] = null;
            }
        }

        return $data;
    }

    public static function canViewAny(): bool
    {
        return Auth::user()->hasRole('super-admin');
    }       
}
