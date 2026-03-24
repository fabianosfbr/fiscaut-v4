<?php


use App\Http\Controllers\IssuerAssembleiaDocumentController;
use App\Http\Controllers\IssuerRagDocumentController;
use App\Http\Controllers\PlanoDeContaSelectController;
use App\Http\Controllers\SecureDownloadController;
use App\Http\Controllers\UploadFileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect(route('filament.app.auth.login'));
})->name('home.index');

Route::middleware(['auth'])->group(function () {
    Route::get('/download/{uuid}', [SecureDownloadController::class, 'download'])
        ->name('download');

    Route::get('/upload-file/{id}', [UploadFileController::class, 'preview'])
        ->name('upload-file.preview');

    Route::get('/issuer-rag/document/{record}', [IssuerRagDocumentController::class, 'show'])
        ->name('issuer-rag.document.show');

    Route::get('/issuer-assembleia/document/{record}', [IssuerAssembleiaDocumentController::class, 'show'])
        ->name('issuer-assembleia.document.show');

    Route::get('filament/remote-select/search', [PlanoDeContaSelectController::class, 'search'])
        ->name('filament.remote-select.search');

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
