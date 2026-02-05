<?php

namespace App\Filament\Resources\CategoryTags\Actions;

use App\Models\CategoryTag;
use App\Models\Tag;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class GerarEtiquetaAction
{
    public static function make()
    {
        return Action::make('gerar_etiquetas')
            ->label('Gerar Etiquetas')
            ->requiresConfirmation()
            ->modalDescription('Esta ação irá gerar as etiquetas padrão para a empresa atual. Deseja continuar?')
            ->visible(fn () => Auth::user()->currentIssuer->categoryTags()->count() === 0)
            ->action(function () {

                $categoryData = config('tags.default');

                foreach ($categoryData as $cat) {

                    $category = new CategoryTag;
                    $category->order = $cat['order'];
                    $category->name = $cat['name'];
                    $category->color = $cat['color'];
                    $category->issuer_id = Auth::user()->currentIssuer->id;
                    $category->tenant_id = Auth::user()->tenant_id;

                    $category->saveQuietly();

                    foreach ($cat['tags'] as $value) {
                        $tag = new Tag;
                        $tag->fill(['name' => $value['name']]);
                        $tag->category_id = $category->id;
                        $tag->issuer_id = $category->issuer_id;
                        $tag->tenant_id = $category->tenant_id;
                        $tag->code = $value['code'];

                        $tag->saveQuietly();
                    }
                }

                $issuerId = Auth::user()->currentIssuer->id;
                Cache::forget("category_tag_{$issuerId}_all");

                Notification::make()
                    ->title('Etiquetas geradas com sucesso')
                    ->success()
                    ->send();
            });
    }
}
