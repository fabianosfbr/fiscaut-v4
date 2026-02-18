<?php

namespace App\Integrations\DominioSistemas;

use App\Integrations\DominioSistemas\Records\IRegistro;
use App\Integrations\DominioSistemas\Records\Registro0000;
use App\Integrations\DominioSistemas\Records\RegistroFactory;

/**
 * Serviço para geração de arquivos TXT conforme layout da Domínio Sistemas
 */
class DominioSistemasService
{
    /**
     * Gera o conteúdo TXT para um conjunto de registros
     *
     * @param array $registros
     * @param string|null $inscricaoEmpresa
     * @return string
     */
    public function gerarConteudoTxt(array $registros, ?string $inscricaoEmpresa = null): string
    {
        $linhas = [];
        
        // Adiciona o registro de cabeçalho (0000) se não estiver presente e a inscrição da empresa for fornecida
        if ($inscricaoEmpresa && !$this->contemRegistro0000($registros)) {
            $registro0000 = new Registro0000($inscricaoEmpresa);
            if ($registro0000->isValid()) {
                $linhas[] = $registro0000->converterParaLinhaTxt();
            }
        }
        
        // Adiciona as linhas dos registros fornecidos
        foreach ($registros as $registro) {
            if ($registro instanceof IRegistro) {
                if ($registro->isValid()) {
                    $linhas[] = $registro->converterParaLinhaTxt();
                }
            }
        }
        
        // Junta todas as linhas com quebras
        $conteudo = implode("\n", $linhas);
        
        // Converte para o encoding correto (Windows-1252)
        return mb_convert_encoding($conteudo, 'Windows-1252', 'UTF-8');
    }
    
    /**
     * Salva o conteúdo TXT em um arquivo
     *
     * @param string $conteudo
     * @param string $caminhoArquivo
     * @return bool
     */
    public function salvarArquivoTxt(string $conteudo, string $caminhoArquivo): bool
    {
        // Certifica-se de que o diretório existe
        $diretorio = dirname($caminhoArquivo);
        if (!is_dir($diretorio)) {
            mkdir($diretorio, 0755, true);
        }
        
        // Escreve o conteúdo no arquivo
        $bytesEscritos = file_put_contents($caminhoArquivo, $conteudo, LOCK_EX);
        
        return $bytesEscritos !== false;
    }
    
    /**
     * Gera e salva um arquivo TXT com os registros fornecidos
     *
     * @param array $registros
     * @param string $caminhoArquivo
     * @param string|null $inscricaoEmpresa
     * @return bool
     */
    public function gerarArquivoTxt(array $registros, string $caminhoArquivo, ?string $inscricaoEmpresa = null): bool
    {
        $conteudo = $this->gerarConteudoTxt($registros, $inscricaoEmpresa);
        return $this->salvarArquivoTxt($conteudo, $caminhoArquivo);
    }
    
    /**
     * Verifica se o array de registros contém um Registro0000
     *
     * @param array $registros
     * @return bool
     */
    private function contemRegistro0000(array $registros): bool
    {
        foreach ($registros as $registro) {
            if ($registro instanceof Registro0000) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Cria um registro usando a factory e adiciona à lista de registros
     *
     * @param string $tipoRegistro
     * @param array $dados
     * @return IRegistro
     */
    public function criarRegistro(string $tipoRegistro, array $dados): IRegistro
    {
        return RegistroFactory::criarRegistro($tipoRegistro, $dados);
    }
}