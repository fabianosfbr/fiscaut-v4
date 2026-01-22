<?php

namespace App\Services;

use Exception;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

class CnpjJaService
{
    public static function getCnpjDetails(string $cnpj): array
    {
        try {
            $url = "https://api.cnpja.com/office/{$cnpj}";
            $response = Http::withHeaders([
                'Authorization' => config('admin.cnpj_ja_api_key'),
            ])->get($url);

            if ($response->failed()) {
                throw new Exception('Falha na consulta ao CNPJ.já: '.$response->status().' - '.$response->body());
            }

            return $response->json();
        } catch (RequestException $e) {
            throw new Exception('Erro de conexão com a API CNPJ.já: '.$e->getMessage());
        } catch (Exception $e) {
            throw $e;
        }
    }
}
