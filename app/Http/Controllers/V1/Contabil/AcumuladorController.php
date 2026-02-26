<?php

namespace App\Http\Controllers\V1\Contabil;

use App\Http\Controllers\Controller;
use App\Models\Acumulador;
use App\Models\Issuer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AcumuladorController extends Controller
{
    public function index(Request $request)
    {
        $clientes = Acumulador::paginate(5);

        return response()->json($clientes);
    }

    public function store(Request $request)
    {

        $data = $request->validate([

            'codi_acu' => 'required',
            'nome_acu' => 'required',
            'cnpj_empresa' => 'required',
        ]);

        $owner = Auth::user();

        $issuer = Issuer::where('cnpj', $data['cnpj_empresa'])
            ->where('tenant_id', $owner->tenant_id)
            ->first();

        Acumulador::updateOrCreate(
            [
                'issuer_id' => $issuer->id,
                'codi_acu' => $data['codi_acu'],

            ],

            [
                'issuer_id' => $issuer->id,
                'codi_acu' => $data['codi_acu'],
                'nome_acu' => $data['nome_acu'],
            ]

        );

        return response()->json(['status' => true, 'message' => 'Acumulador criado com sucesso'], 201);
    }
}
