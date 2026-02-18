<?php

namespace App\Integrations\DominioSistemas\Records;

use App\Integrations\DominioSistemas\Records\Registro0010;
use App\Integrations\DominioSistemas\Records\Registro0020;
use App\Integrations\DominioSistemas\Records\Registro0030;
use App\Integrations\DominioSistemas\Records\Registro0135;

/**
 * Factory para criação de instâncias de registros específicos
 */
class RegistroFactory
{
    /**
     * Cria uma instância de registro com base no tipo
     *
     * @param string $tipoRegistro
     * @param array $dados
     * @return IRegistro
     */
    public static function criarRegistro(string $tipoRegistro, array $dados): IRegistro
    {
        switch ($tipoRegistro) {
            case '0000':
                return new Registro0000($dados['inscricao_empresa'] ?? '');
            
            case '0010':
                if (!isset($dados['inscricao'], $dados['razao_social'])) {
                    throw new \InvalidArgumentException('Dados insuficientes para criar Registro0010');
                }
                
                $registro = new Registro0010(
                    $dados['inscricao'],
                    $dados['razao_social']
                );
                
                // Preencher campos adicionais se fornecidos
                if (isset($dados['apelido'])) {
                    $registro->setApelido($dados['apelido']);
                }
                if (isset($dados['endereco'])) {
                    $registro->setEndereco($dados['endereco']);
                }
                if (isset($dados['uf'])) {
                    $registro->setUf($dados['uf']);
                }
                if (isset($dados['cep'])) {
                    $registro->setCep($dados['cep']);
                }
                
                return $registro;
            
            case '0020':
                if (!isset($dados['inscricao'], $dados['razao_social'])) {
                    throw new \InvalidArgumentException('Dados insuficientes para criar Registro0020');
                }
                
                $registro = new Registro0020(
                    $dados['inscricao'],
                    $dados['razao_social']
                );
                
                // Preencher campos adicionais se fornecidos
                if (isset($dados['apelido'])) {
                    $registro->setApelido($dados['apelido']);
                }
                if (isset($dados['endereco'])) {
                    $registro->setEndereco($dados['endereco']);
                }
                if (isset($dados['uf'])) {
                    $registro->setUf($dados['uf']);
                }
                if (isset($dados['cep'])) {
                    $registro->setCep($dados['cep']);
                }
                
                return $registro;
            
            case '0100':
                $registro = new Registro0100(
                    $dados['codigo_produto'] ?? '',
                    $dados['descricao_produto'] ?? ''
                );
                
                // Preencher campos adicionais se fornecidos
                if (isset($dados['codigo_ncm'])) {
                    $registro->setCodigoNcm($dados['codigo_ncm']);
                }
                if (isset($dados['codigo_barras'])) {
                    $registro->setCodigoBarras($dados['codigo_barras']);
                }
                if (isset($dados['unidade_medida'])) {
                    $registro->setUnidadeMedida($dados['unidade_medida']);
                }
                if (isset($dados['valor_unitario'])) {
                    $registro->setValorUnitario((float)$dados['valor_unitario']);
                }
                
                return $registro;
            
            case '0030':
                if (!isset($dados['inscricao'], $dados['razao_social'])) {
                    throw new \InvalidArgumentException('Dados insuficientes para criar Registro0030');
                }
                
                $registro = new Registro0030(
                    $dados['inscricao'],
                    $dados['razao_social']
                );
                
                // Preencher campos adicionais se fornecidos
                if (isset($dados['endereco'])) {
                    $registro->setEndereco($dados['endereco']);
                }
                if (isset($dados['uf'])) {
                    $registro->setUf($dados['uf']);
                }
                if (isset($dados['codigo_municipio'])) {
                    $registro->setCodigoMunicipio($dados['codigo_municipio']);
                }
                if (isset($dados['inscricao_estadual'])) {
                    $registro->setInscricaoEstadual($dados['inscricao_estadual']);
                }
                if (isset($dados['tipo_inscricao'])) {
                    $registro->setTipoInscricao($dados['tipo_inscricao']);
                }
                
                return $registro;
            
            case '0135':
                if (!isset($dados['codigo_produto'], $dados['data'], $dados['valor_unitario'])) {
                    throw new \InvalidArgumentException('Dados insuficientes para criar Registro0135');
                }
                
                $data = $dados['data'] instanceof \DateTime 
                    ? $dados['data'] 
                    : new \DateTime($dados['data']);
                
                return new Registro0135(
                    $dados['codigo_produto'],
                    $data,
                    (float)$dados['valor_unitario']
                );
            
            case '1000':
                if (!isset($dados['codigo_especie'], $dados['inscricao_fornecedor'], $dados['cfop'], 
                          $dados['numero_documento'], $dados['data_entrada'], $dados['data_emissao'],
                          $dados['valor_contabil'], $dados['valor_produtos'], $dados['municipio_origem'],
                          $dados['situacao_nota'])) {
                    throw new \InvalidArgumentException('Dados insuficientes para criar Registro1000');
                }
                
                $dataEntrada = $dados['data_entrada'] instanceof \DateTime 
                    ? $dados['data_entrada'] 
                    : new \DateTime($dados['data_entrada']);
                    
                $dataEmissao = $dados['data_emissao'] instanceof \DateTime 
                    ? $dados['data_emissao'] 
                    : new \DateTime($dados['data_emissao']);
                
                return new Registro1000(
                    $dados['codigo_especie'],
                    $dados['inscricao_fornecedor'],
                    $dados['cfop'],
                    (int)$dados['numero_documento'],
                    $dataEntrada,
                    $dataEmissao,
                    (float)$dados['valor_contabil'],
                    (float)$dados['valor_produtos'],
                    $dados['municipio_origem'],
                    (int)$dados['situacao_nota']
                );
            
            default:
                throw new \InvalidArgumentException("Tipo de registro desconhecido: {$tipoRegistro}");
        }
    }
    
    /**
     * Valida se um tipo de registro é suportado
     *
     * @param string $tipoRegistro
     * @return bool
     */
    public static function tipoRegistroSuportado(string $tipoRegistro): bool
    {
        $tiposSuportados = ['0000', '0010', '0020', '0030', '0100', '0135', '1000'];
        return in_array($tipoRegistro, $tiposSuportados);
    }
}