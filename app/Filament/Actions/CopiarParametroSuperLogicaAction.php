<?php

namespace App\Filament\Actions;

use App\Models\Issuer;
use App\Models\ParametroSuperLogica;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Support\Enums\Width;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;

class CopiarParametroSuperLogicaAction
{
    public static function make(): Action
    {
        return Action::make('copiarParametros')
            ->label('Copiar Parâmetros')
            ->icon('heroicon-o-document-duplicate')
            ->modalHeading('Copiar Parâmetros para Outra Empresa')
            ->modalWidth(Width::ExtraLarge)
            ->modalDescription(new HtmlString('
                    <div class="space-y-2 text-sm">
                        <p>Esta ação irá copiar todos os parâmetros da empresa <b>' . currentIssuer()->razao_social . '</b> para a empresa selecionada. </p>
                        <p>Os parâmetros existentes na empresa destino serão <b>removidos e substituídos </b> pelos novos parâmetros. </p>                        
                        <p><b>Esta operação não poderá ser desfeita. </b></p>
                    </div>
                    '))
            ->closeModalByClickingAway(false)
            ->closeModalByEscaping(false)
            ->modalSubmitActionLabel('Sim, copiar')
            ->form([
                Select::make('issuer_id')
                    ->label('Empresa Destino')
                    ->required()
                    ->options(function () {
                        $currentIssuer = currentIssuer();
                        $user = Auth::user();

                        return $user->issuers()
                            ->wherePivot('active', true) // Garante que o vínculo está ativo
                            ->where('is_enabled', true)  // Garante que a empresa está ativa
                            ->where('issuers.id', '!=', $currentIssuer->id)
                            ->pluck('issuers.razao_social', 'issuers.id');
                    })
                    ->searchable()
                    ->preload(),
            ])
            ->action(function (array $data) {
                $currentIssuer = currentIssuer();
                $targetIssuerId = $data['issuer_id'];

                $parametrosOriginais = ParametroSuperLogica::where('issuer_id', $currentIssuer->id)->get();

                if ($parametrosOriginais->isEmpty()) {
                    Notification::make()
                        ->warning()
                        ->title('Nenhum parâmetro para copiar')
                        ->body('A empresa atual não possui parâmetros para serem copiados.')
                        ->duration(5000)
                        ->send();

                    return;
                }
                $targetIssuer = Issuer::find($targetIssuerId);
                ParametroSuperLogica::where('issuer_id', $targetIssuerId)->delete();

                foreach ($parametrosOriginais as $parametro) {
                    ParametroSuperLogica::create([
                        'issuer_id' => $targetIssuerId,
                        'params' => $parametro->params,
                        'conta_credito' => $parametro->conta_credito,
                        'conta_debito' => $parametro->conta_debito,
                        'codigo_historico' => $parametro->codigo_historico,
                        'check_value' => $parametro->check_value,
                    ]);
                }

                Notification::make()
                    ->success()
                    ->title('Parâmetros copiados com sucesso!')
                    ->body("{$parametrosOriginais->count()} parâmetros foram copiados para {$targetIssuer->razao_social}.")
                    ->send();
            });
    }
}
