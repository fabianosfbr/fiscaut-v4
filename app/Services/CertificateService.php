<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Crypt;

class CertificateService
{
    /**
     * Valida e extrai informações de um certificado digital PKCS#12
     *
     * @param  string  $pfxContent  Conteúdo binário do arquivo .pfx/.p12
     * @param  string  $password  Senha do certificado
     * @return array Informações extraídas do certificado
     *
     * @throws Exception Quando houver erro na validação
     */
    public function validateAndExtractCertificateInfo(string $pfxContent, string $password): array
    {
        // Tentar modernizar o certificado se for legado
        $modernizedPfx = $this->modernizeCertificate($pfxContent, $password);
        if ($modernizedPfx) {
            $pfxContent = $modernizedPfx;
        }

        // Validar certificado com OpenSSL
        $certInfo = [];
        $cert = openssl_pkcs12_read($pfxContent, $certInfo, $password);

        if (! $cert) {
            throw new Exception('Senha inválida ou arquivo de certificado corrompido. Verifique a senha e tente novamente.');
        }

        // Extrair informações do certificado X.509
        $x509 = openssl_x509_parse($certInfo['cert']);

        if (! $x509) {
            throw new Exception('Erro ao ler os dados do certificado X.509. O arquivo pode estar corrompido.');
        }

        // Verificar se o certificado está válido (não vencido)
        $agora = time();
        $validoAte = $x509['validTo_time_t'];
        $validoDe = $x509['validFrom_time_t'];

        if ($validoAte < $agora) {
            $diasVencido = ceil(($agora - $validoAte) / 86400);
            throw new Exception("Certificado vencido há {$diasVencido} dia(s). Use um certificado válido.");
        }

        if ($validoDe > $agora) {
            throw new Exception('Certificado ainda não está válido. Verifique a data de início da validade.');
        }

        // Extrair CNPJ do subject CN
        $razaoSocial = $x509['subject']['CN'] ?? '';
        $cnpjExtraido = preg_replace('/[^0-9]/', '', $razaoSocial);

        if (strlen($cnpjExtraido) !== 14) {
            throw new Exception('Não foi possível extrair um CNPJ válido do certificado. Verifique se o certificado é de pessoa jurídica.');
        }

        // Verificar se há menos de 30 dias para vencer
        $diasRestantes = ceil(($validoAte - $agora) / 86400);

        return [
            'razao_social' => $razaoSocial,
            'cnpj' => $cnpjExtraido,
            'data_inicio' => date('Y-m-d H:i:s', $validoDe),
            'data_fim' => date('Y-m-d H:i:s', $validoAte),
            'dias_restantes' => $diasRestantes,
            'certificado_content' => Crypt::encrypt($pfxContent),
            'is_expiring_soon' => $diasRestantes <= 30,
            'certificate_info' => $x509, // Informações completas do certificado
        ];
    }

    /**
     * Formata as informações do certificado para exibição
     *
     * @param  array  $certificateData  Dados do certificado
     * @return array Dados formatados
     */
    public function formatCertificateInfoForDisplay(array $certificateData): array
    {
        $diasRestantes = $certificateData['dias_restantes'];

        // Determinar cor e ícone baseado na validade
        $status = [
            'color' => '#059669',
            'icon' => '✅',
            'text' => "Válido por mais {$diasRestantes} ".($diasRestantes === 1 ? 'dia' : 'dias'),
            'alert_level' => 'success',
        ];

        if ($diasRestantes < 0) {
            $diasVencidos = abs($diasRestantes);
            $status = [
                'color' => '#dc2626',
                'icon' => '❌',
                'text' => "Vencido há {$diasVencidos} ".($diasVencidos === 1 ? 'dia' : 'dias'),
                'alert_level' => 'danger',
            ];
        } elseif ($diasRestantes <= 30) {
            $status = [
                'color' => '#f59e0b',
                'icon' => '⚠️',
                'text' => "Vence em {$diasRestantes} ".($diasRestantes === 1 ? 'dia' : 'dias'),
                'alert_level' => 'warning',
            ];
        }

        return [
            'razao_social' => $certificateData['razao_social'],
            'cnpj' => $this->formatCnpj($certificateData['cnpj']),
            'data_inicio_formatada' => $this->formatDate($certificateData['data_inicio']),
            'data_fim_formatada' => $this->formatDate($certificateData['data_fim']),
            'status' => $status,
            'dias_restantes' => $diasRestantes,
        ];
    }

    /**
     * Valida se um arquivo é um certificado PKCS#12 válido
     *
     * @param  string  $filePath  Caminho do arquivo
     * @return bool True se for um certificado válido
     */
    public function isValidCertificateFile(string $filePath): bool
    {
        if (! file_exists($filePath)) {
            return false;
        }

        $content = file_get_contents($filePath);
        if ($content === false) {
            return false;
        }

        // Verificar se é um arquivo PKCS#12 pela assinatura
        return $this->isPkcs12Format($content);
    }

    /**
     * Verifica se o conteúdo é um arquivo PKCS#12
     *
     * @param  string  $content  Conteúdo do arquivo
     * @return bool True se for PKCS#12
     */
    private function isPkcs12Format(string $content): bool
    {
        // PKCS#12 files start with a specific byte sequence
        $pkcs12Signature = "\x30\x82"; // ASN.1 DER encoding signature

        return substr($content, 0, 2) === $pkcs12Signature;
    }

    /**
     * Formata CNPJ para exibição
     *
     * @param  string  $cnpj  CNPJ apenas números
     * @return string CNPJ formatado
     */
    private function formatCnpj(string $cnpj): string
    {
        return preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $cnpj);
    }

    /**
     * Formata data para exibição
     *
     * @param  string  $date  Data no formato Y-m-d H:i:s
     * @return string Data formatada
     */
    private function formatDate(string $date): string
    {
        return date('d/m/Y', strtotime($date));
    }

    /**
     * Extrai informações do emissor do certificado
     *
     * @param  array  $x509Info  Informações X.509
     * @return array Informações do emissor
     */
    public function extractIssuerInfo(array $x509Info): array
    {
        $issuer = $x509Info['issuer'] ?? [];

        return [
            'organization' => $issuer['O'] ?? 'Não informado',
            'common_name' => $issuer['CN'] ?? 'Não informado',
            'country' => $issuer['C'] ?? 'Não informado',
        ];
    }

    /**
     * Verifica se o certificado é apropriado para uso fiscal
     *
     * @param  array  $x509Info  Informações X.509
     * @return bool True se for apropriado para uso fiscal
     */
    public function isTaxCertificate(array $x509Info): bool
    {
        $subject = $x509Info['subject'] ?? [];
        $extensions = $x509Info['extensions'] ?? [];

        // Verificar se tem CNPJ no subject
        $cn = $subject['CN'] ?? '';
        $cnpj = preg_replace('/[^0-9]/', '', $cn);

        if (strlen($cnpj) !== 14) {
            return false;
        }

        // Verificar key usage para assinatura digital
        $keyUsage = $extensions['keyUsage'] ?? '';

        return strpos($keyUsage, 'Digital Signature') !== false;
    }

    /**
     * Descriptografa o conteúdo do certificado armazenado
     *
     * @param  string  $encryptedContent  Conteúdo criptografado
     * @return string Conteúdo descriptografado
     *
     * @throws Exception Se não conseguir descriptografar
     */
    public function decryptCertificateContent(string $encryptedContent): string
    {
        try {
            return Crypt::decrypt($encryptedContent);
        } catch (\Exception $e) {
            throw new Exception('Erro ao descriptografar o certificado armazenado.');
        }
    }

    /**
     * Tenta importar o certificado usando CLI OpenSSL com provider legacy
     * Necessário para certificados com algoritmos antigos em ambientes com OpenSSL 3+
     */
    private function modernizeCertificate(string $pfxContent, string $password): ?string
    {
        $tempPfx = tempnam(sys_get_temp_dir(), 'pfx_old');
        $tempPass = tempnam(sys_get_temp_dir(), 'pass');
        $tempPem = tempnam(sys_get_temp_dir(), 'pem');
        $tempOut = tempnam(sys_get_temp_dir(), 'pfx_new');

        if ($tempPfx === false || $tempPass === false || $tempPem === false || $tempOut === false) {
            return null;
        }

        file_put_contents($tempPfx, $pfxContent);
        file_put_contents($tempPass, $password);

        $output = [];
        $returnVar = 0;

        // Step 1: Extract to PEM using legacy provider
        $cmd1 = sprintf(
            'openssl pkcs12 -in %s -passin file:%s -nodes -legacy -out %s 2>/dev/null',
            escapeshellarg($tempPfx),
            escapeshellarg($tempPass),
            escapeshellarg($tempPem)
        );
        exec($cmd1, $output, $returnVar);

        if ($returnVar === 0) {
            // Step 2: Export back to PKCS12 (modern)
            $output = []; // Reset output for next command
            $cmd2 = sprintf(
                'openssl pkcs12 -export -in %s -out %s -passout file:%s 2>/dev/null',
                escapeshellarg($tempPem),
                escapeshellarg($tempOut),
                escapeshellarg($tempPass)
            );
            exec($cmd2, $output, $returnVar);
        }

        $newPfxContent = null;
        if ($returnVar === 0 && file_exists($tempOut) && filesize($tempOut) > 0) {
            $newPfxContent = file_get_contents($tempOut);
        }

        @unlink($tempPfx);
        @unlink($tempPass);
        @unlink($tempPem);
        @unlink($tempOut);

        return $newPfxContent;
    }
}
