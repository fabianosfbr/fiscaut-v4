<?php

namespace App\Filament\Actions;

use App\Models\Estado;
use App\Services\Sefaz\SefazCteDownloadService;
use App\Services\Xml\XmlReaderService;
use Exception;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Support\Exceptions\Halt;
use Illuminate\Database\Eloquent\Model;

class ManifestarCteAction
{
    public static function make(): Action
    {
        return Action::make('manifestar-cte')
            ->label('Manifestar')
            ->icon('heroicon-o-book-open')
            ->modalWidth('lg')
            ->modalHeading('Manifestar')
            ->modalDescription('Insira os dados para manifestar o conhecimento de transporte eletrônico.')
            ->closeModalByClickingAway(false)
            ->closeModalByEscaping(false)
            ->modalSubmitActionLabel('Sim, manifestar')
            ->schema([
                DatePicker::make('data_entrada')
                    ->label('Data Entrada')
                    ->required()
                    ->format('Y-m-d')
                    ->weekStartsOnSunday()
                    ->default(now())
                    ->displayFormat('d/m/Y'),
                Select::make('status_manifestacao')
                    ->label('Status da Manifestação')
                    ->required()
                    ->default(610110)
                    ->options([
                        '610110' => 'Prestação de serviço em desacordo',
                    ]),
                Textarea::make('justificativa')
                    ->label('Justificativa')
                    ->hint('Minimo de 15 caracteres')
                    ->minLength(15)
                    ->required(),
            ])
            ->action(function (array $data, Model $record) {
                if (empty($record->xml)) {
                    return;
                }

                $statusManifestacao = $data['status_manifestacao'] ?? null;
                $justificativa = $data['justificativa'] ?? '';

                $xmlData = (new XmlReaderService)->read(gzuncompress($record->xml));

                $codUf = $xmlData['cteProc']['CTe']['infCte']['ide']['cUF'];

                $uf = Estado::whereId($codUf)->first()?->sigla;

                $issuer = currentIssuer();

                try {
                    $service = new SefazCteDownloadService($issuer);
                    $manifestado = $service->sefazManifesta($record->chave, $statusManifestacao, $justificativa, 1, $uf);
                } catch (Exception $e) {
                    Notification::make()
                        ->title('Erro ao manifestar CTe')
                        ->body('Falha ao manifestar CTe. Por favor, entre em contato com o administrador. '.$e->getMessage())
                        ->danger()
                        ->send();

                    throw new Halt;
                }
                if ($manifestado->infEvento->cStat == '135' || $manifestado->infEvento->cStat === '631') {
                    $record->update([
                        'status_manifestacao' => $statusManifestacao,
                        'data_manifesto' => date('Y-m-d H:i:s'),
                    ]);

                    Notification::make()
                        ->title('Nota manifestada com sucesso!')
                        ->success()
                        ->send();
                }
            });
    }
}
