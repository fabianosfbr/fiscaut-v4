<?php

use App\Http\Controllers\TempFileController;
use App\Http\Controllers\UploadFileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/upload-file/{id}', [UploadFileController::class, 'preview'])
        ->name('upload-file.preview');

    Route::get('process-download/{tempFile}', [TempFileController::class, 'download'])
        ->name('process-dowload.download');

    // Route::get('nfse/pdf/{id}', [NfsePdfController::class, 'showPdf'])
    //     ->name('nfse.pdf.show');

    // // Rota para download do PDF de faturamento mensal
    // Route::get('app/relatorio-faturamento-mensal/download-pdf', function () {
    //     return app(\App\Filament\App\Pages\RelatorioFaturamentoMensal::class)
    //         ->downloadPdf();
    // })->name('filament.app.pages.relatorio-faturamento-mensal.download-pdf');

    // Route::get('filament/remote-select/search', [PlanoDeContaSelectController::class, 'search'])
    //     ->name('filament.remote-select.search');
});
