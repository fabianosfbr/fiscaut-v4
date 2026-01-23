<?php

namespace App\Filament\Resources\CategoryTags\Pages;

use App\Filament\Resources\CategoryTags\Actions\CopiarEtiquetaAction;
use App\Filament\Resources\CategoryTags\Actions\GerarEtiquetaAction;
use App\Filament\Resources\CategoryTags\CategoryTagResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCategoryTags extends ListRecords
{
    protected static string $resource = CategoryTagResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Cadastrar Nova Etiqueta')
                ->icon('heroicon-o-plus'),
            GerarEtiquetaAction::make(),
            CopiarEtiquetaAction::make(),
        ];
    }
}
