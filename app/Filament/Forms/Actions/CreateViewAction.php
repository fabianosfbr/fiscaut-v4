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

class CreateViewAction extends Action
{
    use CanCustomizeProcess;

    public static function getDefaultName(): ?string
    {
        return 'table_views.save.action';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->model(TableView::class)
            ->schema([
                TextInput::make('name')
                    ->label('Nome')
                    ->autofocus()
                    ->required(),
                IconPicker::make('icon')
                    ->label('Ícone')
                    ->sets(['heroicons'])
                    ->gridSearchResults()
                    ->iconsSearchResults(),
                Toggle::make('is_favorite')
                    ->label('Favoritar')
                    ->helperText('Adicionar aos favoritos'),
                Toggle::make('is_public')
                    ->label('Público')
                    ->helperText('Tornar público'),
            ])->action(function (): void {
                $model = $this->getModel();

                $record = $this->process(function (array $data) use ($model): TableView {
                    $record = new $model;
                    $record->fill($data);
                    $record->save();

                    TableViewFavorite::create([
                        'view_type'       => 'saved',
                        'view_key'        => $record->id,
                        'filterable_type' => $record->filterable_type,
                        'user_id'         => Auth::id(),
                        'is_favorite'     => $data['is_favorite'],
                    ]);

                    return $record;
                });

                $this->record($record);

                $this->success();
            })
            ->label('Adicionar')
            ->link()
            ->successNotificationTitle('Visualização salva')
            ->modalHeading('Salvar visualização')
            ->modalWidth(Width::Medium);
    }
}
