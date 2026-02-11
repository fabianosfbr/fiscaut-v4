<?php

namespace App\Filament\Pages\Configuracoes;

use Filament\Pages\Page;
use Filament\Schemas\Schema;
use App\Services\LogViewService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Symfony\Component\Finder\Finder;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\TextInput;

use Filament\Schemas\Components\Section;
use Symfony\Component\Finder\SplFileInfo;
use Filament\Forms\Components\CheckboxList;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use UnitEnum;

class LogViewPage extends Page
{
    
    protected static ?string $navigationLabel = 'Visualizar Logs';

    protected static ?string $title = 'Visualizar Logs';

    protected static string|UnitEnum|null $navigationGroup = 'Administração';

    protected string $view = 'filament.pages.configuracoes.log-view-page';

    public ?string $logFile = null;

    public ?string $searchTerm = null;

    public array $selectedLevels = ['info', 'warning', 'danger'];

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        Select::make('logFile')
                            ->searchable()
                            ->reactive()
                            ->hiddenLabel()
                            ->placeholder('Buscar arquivo de log...')
                            ->options(fn(): Collection => $this->getFileNames($this->getFinder())->take(10))
                            ->getSearchResultsUsing(fn(string $query): Collection => $this->getFileNames($this->getFinder()->name("*{$query}*"))),

                        TextInput::make('searchTerm')
                            ->hiddenLabel()
                            ->placeholder('Pesquisar no log...')
                            ->live(debounce: 500)
                            ->prefixIcon('heroicon-m-magnifying-glass')
                            ->visible(fn() => $this->logFile !== null),

                        CheckboxList::make('selectedLevels')
                            ->hiddenLabel()
                            ->options([
                                'danger' => 'Error/Critical',
                                'info' => 'Info/Debug',
                                'warning' => 'Warning',
                            ])
                            ->columns(3)
                            ->live()
                            ->visible(fn() => $this->logFile !== null),
                    ]),
            ]);
    }

    public function getLogs(): Collection
    {
        if (!$this->logFile) {
            return collect([]);
        }

        $logs = collect(LogViewService::getAllForFile($this->logFile));

        // Filtro por Nível
        if (!empty($this->selectedLevels)) {
            $logs = $logs->filter(fn($log) => in_array($log['level_class'], $this->selectedLevels));
        } else {
            return collect([]); // Se nada selecionado, nada exibido
        }

        if ($this->searchTerm) {
            $logs = $logs->filter(function ($log) {
                $term = strtolower($this->searchTerm);
                return str_contains(strtolower($log['text']), $term) ||
                    str_contains(strtolower($log['stack']), $term) ||
                    str_contains(strtolower($log['context']), $term) ||
                    str_contains(strtolower($log['date']), $term) ||
                    str_contains(strtolower($log['level']), $term);
            });
        }

        return $logs;
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
