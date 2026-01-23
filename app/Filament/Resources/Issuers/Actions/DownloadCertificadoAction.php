<?php

namespace App\Filament\Resources\Issuers\Actions;

use Filament\Actions\Action;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class DownloadCertificadoAction
{
    public static function make(): Action
    {
        return Action::make('download_certificado')
            ->label('Download do Certificado')
            ->color('primary')
            ->icon('heroicon-o-arrow-down-tray')
            ->disabled(fn (Model $record): bool => empty($record->certificado_content))
            ->action(function (Model $record) {

                $certificadoContent = Crypt::decrypt($record->certificado_content);
                $senhaArquivo = Crypt::decrypt($record->senha_certificado);
                $nomeArquivo = $record->cnpj.' - '.$senhaArquivo.'.pfx';

                return response()->streamDownload(function () use ($certificadoContent) {
                    echo $certificadoContent;
                }, $nomeArquivo, [
                    'Content-Type' => 'application/x-pkcs12',
                ]);
            });
    }
}
