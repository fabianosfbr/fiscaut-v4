<?php

namespace App\Filament\Resources\XmlImportJobs;

use App\Filament\Resources\XmlImportJobs\Pages\CreateXmlImportJob;
use App\Filament\Resources\XmlImportJobs\Pages\EditXmlImportJob;
use App\Filament\Resources\XmlImportJobs\Pages\ListXmlImportJobs;
use App\Filament\Resources\XmlImportJobs\Pages\ViewXmlImportJob;
use App\Filament\Resources\XmlImportJobs\Schemas\XmlImportJobForm;
use App\Filament\Resources\XmlImportJobs\Schemas\XmlImportJobInfolist;
use App\Filament\Resources\XmlImportJobs\Tables\XmlImportJobsTable;
use App\Models\XmlImportJob;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class XmlImportJobResource extends Resource
{
    protected static ?string $model = XmlImportJob::class;

    protected static string|UnitEnum|null $navigationGroup = 'Configurações';

    protected static ?string $navigationLabel = 'Histórico de Importações';

    protected static ?string $slug = 'xml-import-history';

    public static function form(Schema $schema): Schema
    {
        return XmlImportJobForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return XmlImportJobsTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return XmlImportJobInfolist::configure($schema);
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
            'index' => ListXmlImportJobs::route('/'),
            'create' => CreateXmlImportJob::route('/create'),
            'view' => ViewXmlImportJob::route('/{record}'),
            'edit' => EditXmlImportJob::route('/{record}/edit'),
        ];
    }
}
