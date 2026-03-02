<?php

namespace App\Http\Controllers;

use App\Models\PlanoDeConta;
use Illuminate\Http\Request;

class PlanoDeContaSelectController extends Controller
{
    public function search(Request $request)
    {
        $params = $request->get('query');

        $issuerId = currentIssuer()->id;

        // Verifica se o termo de busca foi passado
        if (! $params) {
            return response()->json(['message' => 'Por favor, informe um termo de busca.'], 400);
        }

        $results = [];

        if (is_numeric($params)) {
            // Consulta o código do produto
            $results = PlanoDeConta::where('issuer_id', $issuerId)->where('codigo', $params)->limit(50)
                ->get(['id', 'codigo', 'nome']);
        } else {
            // Consulta o nome do produto usando LIKE
            $results = PlanoDeConta::where('issuer_id', $issuerId)->where('nome', 'LIKE', "%$params%")->limit(50)
                ->get(['id', 'codigo', 'nome']);
        }

        return response()->json($results);
    }
}
