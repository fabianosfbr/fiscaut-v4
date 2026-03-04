<?php

use App\Models\Issuer;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

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

if (! function_exists('currentIssuerCacheKey')) {
    function currentIssuerCacheKey(int $userId, int $issuerId): string
    {
        return "current_issuer:{$userId}:{$issuerId}";
    }
}

if (! function_exists('forgetCurrentIssuerCache')) {
    function forgetCurrentIssuerCache(int $userId, int $issuerId): void
    {
        Cache::forget(currentIssuerCacheKey($userId, $issuerId));
    }
}

if (! function_exists('currentIssuer')) {
    function currentIssuer(?User $user = null): ?Issuer
    {
        $user ??= Auth::user();
        
        if (! $user) {
            return null;
        }

        $issuerId = (int) ($user->issuer_id ?? 0);

        if ($issuerId <= 0) {
            return null;
        }

        if ($user->relationLoaded('currentIssuer')) {            
            return $user->currentIssuer;
        }

        static $memo = [];
        $memoKey = $user->id.':'.$issuerId;

        if (array_key_exists($memoKey, $memo)) {
            return $memo[$memoKey];
        }

        $issuer = Cache::remember(
            currentIssuerCacheKey((int) $user->id, $issuerId),
            now()->addMinutes(10),
            fn () => Issuer::query()->find($issuerId),
        );

        $memo[$memoKey] = $issuer;

        return $issuer;
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

if (! function_exists('canManageIssuers')) {
    function canManageIssuers(): bool
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // return $user->hasRole('super-admin', 'admin');
        return true;
    }
}

if (! function_exists('loadXmlReader')) {
    function loadXmlReader($xml)
    {
        return app(\App\Services\Xml\XmlReaderService::class)->read($xml);
    }
}

if (! function_exists('xml_is_assoc')) {
    function xml_is_assoc(array $value): bool
    {
        $keys = array_keys($value);

        return $keys !== range(0, count($keys) - 1);
    }
}

if (! function_exists('xml_list')) {
    function xml_list($value): array
    {
        if ($value === null) {
            return [];
        }

        if (! is_array($value)) {
            return [$value];
        }

        if ($value === []) {
            return [];
        }

        if (xml_is_assoc($value)) {
            return [$value];
        }

        return $value;
    }
}

if (! function_exists('searchValueInArray')) {
    function searchValueInArray(array $data, $needle)
    {
        $iterator = new RecursiveArrayIterator($data);
        $recursive = new RecursiveIteratorIterator(
            $iterator,
            RecursiveIteratorIterator::SELF_FIRST
        );
        foreach ($recursive as $key => $value) {
            if ($key === $needle) {
                return $value;
            }
        }

        return null;
    }
}

if (! function_exists('prettyPrintXmlToBrowser')) {
    function prettyPrintXmlToBrowser(string $xmlContent)
    {
        $xml = new SimpleXMLElement($xmlContent);
        $domXml = new DOMDocument('1.0');
        $domXml->preserveWhiteSpace = false;
        $domXml->formatOutput = true;
        $domXml->loadXML($xml->asXML());
        $xmlString = $domXml->saveXML();

        return htmlspecialchars($xmlString);
    }
}
