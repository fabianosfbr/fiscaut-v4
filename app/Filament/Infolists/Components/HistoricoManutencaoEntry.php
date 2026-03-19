<?php

namespace App\Filament\Infolists\Components;

use Filament\Infolists\Components\Entry;

class HistoricoManutencaoEntry extends Entry
{
    protected string $view = 'filament.infolists.components.historico-manutencao-entry';

    // protected function setUp(): void
    // {
    //     parent::setUp();

    //     $this->state(function ($record): HtmlString {
    //         $items = $record->historicos()
    //             ->with('usuario')
    //             ->orderByDesc('created_at')
    //             ->limit(20)
    //             ->get();

    //         if ($items->isEmpty()) {
    //             return new HtmlString('<p class="text-sm text-gray-500">Nenhum histórico registrado.</p>');
    //         }

    //         $rows = $items->map(function ($item, $index) use ($items): string {
    //             $date = $item->created_at?->format('d/m/Y H:i') ?? '—';
    //             $user = e($item->usuario?->name ?? 'Sistema');
    //             $action = e($item->descricao_acao ?? $item->acao ?? 'Ação');
    //             $note = e((string) ($item->observacao ?? ''));

    //             $isLast = $index === $items->count() - 1;

    //             $actionLower = strtolower($action);
    //             if (str_contains($actionLower, 'concluíd') || str_contains($actionLower, 'finaliz')) {
    //                 $iconClass = 'bg-success-500';
    //                 $iconSvg = '<path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />';
    //             } elseif (str_contains($actionLower, 'aprov') || str_contains($actionLower, 'avança') || str_contains($actionLower, 'atualiz')) {
    //                 $iconClass = 'bg-primary-500';
    //                 $iconSvg = '<path stroke-linecap="round" stroke-linejoin="round" d="M6.633 10.5c.806 0 1.533-.446 2.031-1.08a9.041 9.041 0 012.861-2.4c.723-.384 1.35-.956 1.653-1.715a4.498 4.498 0 00.322-1.672V3a.75.75 0 01.75-.75A2.25 2.25 0 0116.5 4.5c0 1.152-.26 2.243-.723 3.218-.266.558.107 1.282.725 1.282h3.126c1.026 0 1.945.694 2.054 1.715.045.422.068.85.068 1.285a11.95 11.95 0 01-2.649 7.521c-.388.482-.987.729-1.605.729H13.48c-.483 0-.964-.078-1.423-.23l-3.114-1.04a4.501 4.501 0 00-1.423-.23H5.904M14.25 9h2.25M5.904 18.75c.083.205.173.405.27.602.197.4-.078.898-.523.898h-.908c-.889 0-1.713-.518-1.972-1.368a12 12 0 01-.521-3.507c0-1.553.295-3.036.831-4.398C3.387 10.203 4.167 9.75 5 9.75h1.053c.472 0 .745.556.5.96a8.958 8.958 0 00-1.302 4.665c0 1.194.232 2.333.654 3.375z" />';
    //             } elseif (str_contains($actionLower, 'criad') || str_contains($actionLower, 'iníci') || str_contains($actionLower, 'novo') || str_contains($actionLower, 'abert')) {
    //                 $iconClass = 'bg-gray-500';
    //                 $iconSvg = '<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />';
    //             } else {
    //                 $iconClass = 'bg-gray-400 dark:bg-gray-600';
    //                 $iconSvg = '<path stroke-linecap="round" stroke-linejoin="round" d="M12 20.25c4.97 0 9-3.694 9-8.25s-4.03-8.25-9-8.25S3 7.444 3 12c0 2.104.859 4.023 2.273 5.48.432.447.74 1.04.586 1.641a4.483 4.483 0 01-.923 1.785A5.969 5.969 0 006 21c1.282 0 2.47-.402 3.445-1.087.81.22 1.668.337 2.555.337z" />';
    //             }

    //             $noteHtml = $note !== '' ? "<div class=\"mt-2 text-sm text-gray-600 dark:text-gray-400\">{$note}</div>" : '';
    //             $lineHtml = ! $isLast ? "<span class=\"absolute top-5 left-4 -ml-px h-full w-0.5 bg-gray-200 dark:bg-gray-700\" aria-hidden=\"true\"></span>" : '';

    //             return <<<HTML
    //                 <li class="relative pb-8">
    //                     {$lineHtml}
    //                     <div class="relative flex items-start space-x-3">
    //                         <div class="relative flex-none">
    //                             <span class="flex h-8 w-8 items-center justify-center rounded-full {$iconClass} ring-8 ring-white dark:ring-gray-900">
    //                                 <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
    //                                     {$iconSvg}
    //                                 </svg>
    //                             </span>
    //                         </div>
    //                         <div class="min-w-0 flex-1 py-1.5 flex flex-col sm:flex-row sm:justify-between sm:items-center space-y-1 sm:space-y-0 sm:space-x-4">
    //                             <div class="text-sm text-gray-600 dark:text-gray-400">
    //                                 {$action} por <span class="font-medium text-gray-900 dark:text-gray-100">{$user}</span>
    //                                 {$noteHtml}
    //                             </div>
    //                             <div class="flex-none text-sm text-gray-500 dark:text-gray-400">
    //                                 {$date}
    //                             </div>
    //                         </div>
    //                     </div>
    //                 </li>
    //             HTML;
    //         })->implode('');

    //         return new HtmlString("<div class=\"flow-root\"><ul role=\"list\" class=\"-mb-8\">{$rows}</ul></div>");
    //     });
    // }
}
