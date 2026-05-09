<?php

use App\Http\Controllers\V1\Contabil\AcumuladorController;
use App\Http\Controllers\V1\Contabil\ClienteController;
use App\Http\Controllers\V1\Contabil\FornecedorController;
use App\Http\Controllers\V1\Contabil\PlanoDeContaController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['auth:sanctum']], function () {

    Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
        return $request->user();
    });

    Route::group(['middleware' => 'auth:sanctum'], function () {

        Route::get('up', function () {
            return response()->json(['status' => true, 'message' => 'up']);
        });

        Route::post('/contabil/clientes', [ClienteController::class, 'store'])
            ->name('contabil.clientes.store');

        Route::get('/contabil/clientes', [ClienteController::class, 'index'])
            ->name('contabil.clientes.index');

        Route::post('/contabil/fornecedores', [FornecedorController::class, 'store'])
            ->name('contabil.fornecedores.store');

        Route::post('/contabil/plano-de-contas', [PlanoDeContaController::class, 'store'])
            ->name('contabil.planodeconta.store');

        Route::post('/contabil/acumuladores', [AcumuladorController::class, 'store'])
            ->name('contabil.acumulador.store');
    });
});
