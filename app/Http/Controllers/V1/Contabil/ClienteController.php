<?php

namespace App\Http\Controllers\V1\Contabil;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\Issuer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClienteController extends Controller
{
    public function index(Request $request)
    {
        $clientes = Cliente::paginate(5);

        return response()->json($clientes);
    }

    public function store(Request $request)
    {
        $data = $request->validate([

            'nome_cliente' => 'required',
            'cnpj_cliente' => 'required',
            'conta_contabil_cliente' => 'sometimes',
            'cnpj_empresa' => 'required',
        ]);

        $owner = Auth::user();

        $issuer = Issuer::where('cnpj', $data['cnpj_empresa'])
            ->where('tenant_id', $owner->tenant_id)
            ->first();

        Cliente::updateOrCreate(
            [
                'issuer_id' => $issuer->id,
                'cnpj' => $data['cnpj_cliente'],

            ],

            [
                'nome' => $data['nome_cliente'],
                'cnpj' => $data['cnpj_cliente'],
                'conta_contabil' => intval($data['conta_contabil_cliente']) ?? null,
                'issuer_id' => $issuer->id,
            ]
        );

        return response()->json(['status' => true, 'message' => 'Cliente criado com sucesso'], 201);
    }
}
