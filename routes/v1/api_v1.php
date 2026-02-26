<?php

use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['auth:sanctum']], function () {

    Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
        return $request->user();
    });


    Route::group(['middleware' => 'auth:sanctum'], function () {

        Route::get('up', function () {
            return response()->json(['status' => true, 'message' => 'up']);
        });

        Route::post('/contabil/clientes', [\App\Http\Controllers\V1\Contabil\ClienteController::class, 'store'])
            ->name('contabil.clientes.store');

        Route::get('/contabil/clientes', [\App\Http\Controllers\V1\Contabil\ClienteController::class, 'index'])
            ->name('contabil.clientes.index');


        Route::post('/contabil/fornecedores', [\App\Http\Controllers\V1\Contabil\FornecedorController::class, 'store'])
            ->name('contabil.fornecedores.store');

        Route::post('/contabil/plano-de-contas', [\App\Http\Controllers\V1\Contabil\PlanoDeContaController::class, 'store'])
            ->name('contabil.planodeconta.store');


        Route::post('/contabil/acumuladores', [\App\Http\Controllers\V1\Contabil\AcumuladorController::class, 'store'])
            ->name('contabil.acumulador.store');
    });
});
