<?php

namespace App\Filament\Condominio\Resources\IssuerAges;

use App\Enums\IssuerAgeTypeEnum;
use App\Filament\Condominio\Resources\IssuerAges\Pages\CreateIssuerAge;
use App\Filament\Condominio\Resources\IssuerAges\Pages\EditIssuerAge;
use App\Filament\Condominio\Resources\IssuerAges\Pages\ListIssuerAges;
use App\Filament\Condominio\Resources\IssuerAges\Pages\ViewIssuerAge;
use App\Filament\Condominio\Resources\IssuerAges\Schemas\IssuerAgeForm;
use App\Filament\Condominio\Resources\IssuerAges\Schemas\IssuerAgeInfolist;
use App\Filament\Condominio\Resources\IssuerAges\Tables\IssuerAgesTable;
use App\Models\IssuerAge;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class IssuerAgeResource extends Resource
{
    protected static ?string $model = IssuerAge::class;

    protected static ?string $modelLabel = 'Assembleia';

    protected static ?string $pluralModelLabel = 'Assembleias';

    protected static string|UnitEnum|null $navigationGroup = 'AGE/AGO';

    public static function form(Schema $schema): Schema
    {
        return IssuerAgeForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return IssuerAgeInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return IssuerAgesTable::configure($table);
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
            'index' => ListIssuerAges::route('/'),
            // 'create' => CreateIssuerAge::route('/create'),
            // 'edit' => EditIssuerAge::route('/{record}/edit'),
            'view' => ViewIssuerAge::route('/{record}/view'),
        ];
    }

    public static function cleanData(array $data): array
    {
        $type = $data['type'] ?? null;

        if (!$type)
            return $data;

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
            $data['tem_isencao_remuneracao'] = false;
            $data['quem_recebe_isencao'] = null;
            $data['valor_isencao_remuneracao'] = null;
        } elseif ($type === IssuerAgeTypeEnum::AGO->value) {
            // Clear AGE fields
            $data['vigencia_date'] = null;
            $data['prazo_tecnico'] = null;
        }

        return $data;
    }
}
