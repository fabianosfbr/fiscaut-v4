<?php

namespace App\Filament\Resources\CategoryTags\Actions;

use App\Models\CategoryTag;
use App\Models\Issuer;
use App\Models\Tag;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Support\Enums\Width;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\HtmlString;

class CopiarEtiquetaAction
{
    public static function make()
    {
        return Action::make('copiar_etiquetas')
            ->label('Copiar Etiquetas')
            ->modalWidth(Width::Large)
            ->icon('heroicon-o-clipboard-document-list')
            ->visible(fn () => Auth::user()->currentIssuer->categoryTags()->count() === 0)
            ->modalSubmitActionLabel('Sim, copiar etiquetas')
            ->modalCancelActionLabel('Cancelar')
            ->modalHeading('Copiar Etiquetas de Outra Empresa')
            ->modalCancelAction(false)
            ->schema(self::getCopyTagsSchema())
            ->action(fn (array $data) => self::executeCopyTags($data));
    }

    protected static function getCopyTagsSchema()
    {
        return [
            TextEntry::make('category_id')
                ->hiddenLabel()
                ->state(self::getModalDescription()),
            Select::make('issuer_id')
                ->label('Selecionar Empresa')
                ->options(function () {
                    return Issuer::where('id', '!=', Auth::user()->currentIssuer->id)
                        ->where('tenant_id', Auth::user()->tenant_id)
                        ->pluck('razao_social', 'id')
                        ->toArray();
                })
                ->searchable()
                ->preload()
                ->required()
                ->columnSpanFull(),
        ];
    }

    protected static function getModalDescription()
    {
        $razao_social = explode(':', Auth::user()->currentIssuer->razao_social)[0];
        $message = 'Este processo irá remover todas as etiquetas da <b>'.$razao_social.'</b> e novas etiquetas serão criadas. ';
        $message .= '<br/><br/>Cuidado! Esta ação não poderá ser desfeita. ';

        return new HtmlString($message);
    }

    protected static function executeCopyTags(array $data)
    {
        $currentCategorys = CategoryTag::with('tags')->where('issuer_id', Auth::user()->currentIssuer->id)->get();

        foreach ($currentCategorys as $key => $category) {
            $category->tags()->delete();
            $category->delete();
        }

        $categorys = CategoryTag::with('tags')->where('issuer_id', $data['issuer_id'])->get();

        foreach ($categorys as $cat) {
            $category = new CategoryTag;
            $category->name = $cat->name;
            $category->is_enable = $cat->is_enable;
            $category->order = $cat->order;
            $category->color = $cat->color;
            $category->grupo = $cat->grupo;
            $category->conta_contabil = $cat->conta_contabil;
            $category->is_difal = $cat->is_difal;
            $category->is_devolucao = $cat->is_devolucao;
            $category->is_difal = $cat->is_difal;
            $category->tenant_id = $cat->tenant_id;
            $category->issuer_id = $data['issuer_id'];

            $category->saveQuietly();

            foreach ($cat->tags as $value) {
                $tag = new Tag;
                $tag->fill(['name' => $value['name']]);
                $tag->category_id = $category->id;
                $tag->tenant_id = $value->tenant_id;
                $tag->issuer_id = $data['issuer_id'];
                $tag->code = $value->code;
                $tag->saveQuietly();
            }
        }

        $issuerId = Auth::user()->currentIssuer->id;
        Cache::forget("category_tag_{$issuerId}_all");

        Notification::make()
            ->title('Etiquetas copiadas com sucesso')
            ->success()
            ->send();
    }
}
