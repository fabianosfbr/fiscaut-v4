<?php

namespace App\Http\Controllers\V1\Contabil;

use App\Http\Controllers\Controller;
use App\Models\Issuer;
use App\Models\PlanoDeConta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PlanoDeContaController extends Controller
{


    public function index(Request $request)
    {
        $clientes = PlanoDeConta::paginate(5);
        return response()->json($clientes);
    }


    public function store(Request $request)
    {

        $data = $request->validate([

            'codigo' => 'required',
            'classificacao' => 'required',
            'nome' => 'required',
            'tipo' => 'required',
            'cnpj_empresa' => 'required',
        ]);

        $owner = Auth::user();

        $issuer = Issuer::where('cnpj', $data['cnpj_empresa'])
            ->where('tenant_id', $owner->tenant_id)
            ->first();

        PlanoDeConta::updateOrCreate(
            [
                'issuer_id' => $issuer->id,
                'tenant_id' => $owner->tenant_id,
                'codigo' => $data['codigo'],
                'classificacao' => $data['classificacao'],

            ],

            [
                'codigo' => $data['codigo'],
                'classificacao' => $data['classificacao'],
                'nome' => $data['nome'],
                'tipo' => $data['tipo'],
                'issuer_id' => $issuer->id,
                'tenant_id' => $owner->tenant_id,
            ]

        );

        return response()->json(['status' => true, 'message' => 'Plano de conta criado com sucesso'], 201);
    }
}
