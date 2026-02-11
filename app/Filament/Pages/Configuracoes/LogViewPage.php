<?php

namespace App\Filament\Pages\Configuracoes;

use Filament\Pages\Page;
use Filament\Schemas\Schema;
use App\Services\LogViewService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Symfony\Component\Finder\Finder;
use Filament\Forms\Components\Select;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class LogViewPage extends Page
{
    protected string $view = 'filament.pages.configuracoes.log-view-page';


    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('logFile')
                    ->searchable()
                    ->reactive()
                    ->hiddenLabel()
                    ->placeholder('Buscar arquivo de log...')
                    ->options(fn(): Collection => $this->getFileNames($this->getFinder())->take(10))
                    ->getSearchResultsUsing(fn(string $query): Collection => $this->getFileNames($this->getFinder()->name("*{$query}*"))),
            ]);
    }

    public ?string $logFile = null;

    public function getLogs(): Collection
    {
        if (!$this->logFile) {
            return collect([]);
        }

        $logs = LogViewService::getAllForFile($this->logFile);

        return collect($logs);
    }

    /**
     * @throws Exception
     */
    public function download(): BinaryFileResponse
    {


        return response()->download(LogViewService::pathToLogFile($this->logFile));
    }

    /**
     * @throws Exception
     */
    public function delete(): bool
    {
        if (File::delete(LogViewService::pathToLogFile($this->logFile))) {
            $this->logFile = null;

            return true;
        }

        return false;
    }

    protected function getFileNames($files): Collection
    {
        return collect($files)
            ->sortByDesc(fn(SplFileInfo $file): string => $file->getFilename())
            ->mapWithKeys(fn(SplFileInfo $file): array => [$file->getRealPath() => $file->getRealPath()]);
    }

    protected function getFinder(): Finder
    {
        return Finder::create()
            ->ignoreDotFiles(true)
            ->ignoreUnreadableDirs()
            ->files()
            ->in(storage_path('logs'));
    }
}
