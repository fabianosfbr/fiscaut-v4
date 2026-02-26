<?php

namespace App\Http\Controllers\V1\Contabil;

use App\Http\Controllers\Controller;
use App\Models\Fornecedor;
use App\Models\Issuer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FornecedorController extends Controller
{


    public function index(Request $request)
    {
        $clientes = Fornecedor::paginate(5);
        return response()->json($clientes);
    }


    public function store(Request $request)
    {
        $data = $request->validate([

            'nome_fornecedor' => 'required',
            'cnpj_fornecedor' => 'required',
            'conta_contabil_fornecedor' => 'sometimes',
            'cnpj_empresa' => 'required',
        ]);

        $owner = Auth::user();

        $issuer = Issuer::where('cnpj', $data['cnpj_empresa'])
            ->where('tenant_id', $owner->tenant_id)
            ->first();

        Fornecedor::updateOrCreate(
            [
                'issuer_id' => $issuer->id,
                'cnpj' => $data['cnpj_fornecedor'],

            ],

            [
                'nome' => $data['nome_fornecedor'],
                'cnpj' => $data['cnpj_fornecedor'],
                'conta_contabil' => intval($data['conta_contabil_fornecedor']) ?? null,
                'issuer_id' => $issuer->id,
            ]
        );

        return response()->json(['status' => true, 'message' => 'Fornecedor criado com sucesso'], 201);
    }
}
