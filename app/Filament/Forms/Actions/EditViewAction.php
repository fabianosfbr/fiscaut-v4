<?php

namespace App\Filament\Forms\Actions;

use App\Models\TableView;
use App\Models\TableViewFavorite;
use Filament\Actions\Action;
use Filament\Actions\Concerns\CanCustomizeProcess;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Support\Enums\Width;
use Guava\IconPicker\Forms\Components\IconPicker;
use Illuminate\Support\Facades\Auth;

class EditViewAction extends Action
{
    use CanCustomizeProcess;

    public static function getDefaultName(): ?string
    {
        return 'table_views.update.action';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->model(TableView::class)
            ->fillForm(function (array $arguments): array {
                $tableView = TableView::find($arguments['view_key']);

                $tableViewFavorite = TableViewFavorite::query()
                    ->where('user_id', Auth::id())
                    ->where('view_type', 'saved')
                    ->where('view_key', $tableView->id)
                    ->where('filterable_type', $tableView->filterable_type)
                    ->first();

                return [
                    'name'        => $tableView->name,
                    'color'       => $tableView->color,
                    'icon'        => $tableView->icon,
                    'is_favorite' => $tableViewFavorite?->is_favorite ?? false,
                    'is_public'   => $tableView->is_public,
                ];
            })
            ->schema([
                TextInput::make('name')
                    ->label('Nome')
                    ->autofocus()
                    ->required(),
                IconPicker::make('icon')
                    ->label('Ícone')
                    ->sets(['heroicons'])
                    ->columns(4)
                    ->gridSearchResults()
                    ->iconsSearchResults(),
                Toggle::make('is_favorite')
                    ->label('Adicionar aos favoritos')
                    ->helperText('Adicionar aos favoritos'),
                Toggle::make('is_public')
                    ->label('Tornar público')
                    ->helperText('Tornar público'),
            ])->action(function (array $arguments): void {
                $tableView = TableView::find($arguments['view_key']);

                $this->process(function (array $data) use ($tableView): TableView {
                    $tableView->fill($data);
                    $tableView->save();

                    TableViewFavorite::updateOrCreate(
                        [
                            'view_type'       => 'saved',
                            'view_key'        => $tableView->id,
                            'filterable_type' => $tableView->filterable_type,
                            'user_id'         => Auth::id(),
                        ],
                        [
                            'is_favorite' => $data['is_favorite'],
                        ]
                    );

                    return $tableView;
                });

                $this->record($tableView);

                $this->success();
            })
            ->label('Editar')
            ->successNotificationTitle('Visão editada com sucesso')
            ->icon('heroicon-s-pencil-square')
            ->modalHeading('Editar visão')
            ->modalWidth(Width::Medium);
    }
}
