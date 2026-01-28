<?php

namespace App\Filament\Resources\Schedules\Pages;

use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\Schedules\ScheduleResource;

class EditSchedule extends EditRecord
{
    protected static string $resource = ScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()
                ->label('Ver histórico')
                ->visible(fn($record) => $record->histories()->count()),

        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
