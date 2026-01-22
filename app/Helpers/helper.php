<?php

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

if (! function_exists('getMesesAnterioresEPosteriores')) {
    function getMesesAnterioresEPosteriores()
    {
        $meses = [];
        $dataAtual = Carbon::now();

        // Adiciona os 6 meses anteriores
        for ($i = 6; $i > 0; $i--) {
            $mes = $dataAtual->copy()->subMonths($i);
            $chave = $mes->format('Y-m-01'); // Chave no formato '2025-12-01'
            $valor = ucfirst($mes->translatedFormat('F - Y'));
            $meses[$chave] = $valor;
        }

        // Adiciona o mês atual
        $chave = $dataAtual->format('Y-m-01'); // Chave no formato '2025-12-01'
        $valor = ucfirst($dataAtual->translatedFormat('F - Y'));
        $meses[$chave] = $valor;

        // Adiciona os 6 meses posteriores
        for ($i = 1; $i <= 6; $i++) {
            $mes = $dataAtual->copy()->addMonths($i);
            $chave = $mes->format('Y-m-01'); // Chave no formato '2025-12-01'
            $valor = ucfirst($mes->translatedFormat('F - Y'));
            $meses[$chave] = $valor;
        }

        $meses = array_reverse($meses);

        return $meses;
    }
}

if (! function_exists('getLabelTag')) {
    function getLabelTag($str)
    {

        $acronym = null;
        $word = null;

        $words = preg_split("/(\s|\-|\.)/", $str);
        foreach ($words as $w) {
            $acronym .= substr($w, 0, 1);
        }
        $word = $word.$acronym;

        return strtoupper($word);
    }
}

if (! function_exists('formatar_moeda')) {
    function formatar_moeda($value)
    {
        return number_format($value, 2, ',', '.');
    }
}

if (! function_exists('formatar_cep')) {
    function formatar_cep($value)
    {
        return substr($value, 0, 5).'-'.substr($value, 5, 3);
    }
}

if (! function_exists('formatar_cnpj_cpf')) {
    function formatar_cnpj_cpf($value)
    {
        $CPF_LENGTH = 11;
        $cnpj_cpf = preg_replace("/\D/", '', $value);

        if (strlen($cnpj_cpf) === $CPF_LENGTH) {
            return preg_replace("/(\d{3})(\d{3})(\d{3})(\d{2})/", '$1.$2.$3-$4', $cnpj_cpf);
        }

        return preg_replace("/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/", '$1.$2.$3/$4-$5', $cnpj_cpf);
    }
}

if (! function_exists('sanitize')) {
    function sanitize(?string $data): ?string
    {
        if (is_null($data)) {
            return null;
        }

        return (string) preg_replace('/[^A-Za-z0-9]/', '', $data);
    }
}


