<?php

namespace Tests\Feature\Integrations\DominioSistemas;

use App\Integrations\DominioSistemas\DominioSistemasService;
use App\Integrations\DominioSistemas\Records\Registro0000;
use App\Integrations\DominioSistemas\Records\Registro0100;
use App\Integrations\DominioSistemas\Records\Registro1000;
use App\Integrations\DominioSistemas\Records\RegistroFactory;
use PHPUnit\Framework\TestCase;

class DominioSistemasServiceTest extends TestCase
{
    public function test_registro_base_can_format_fields()
    {
        // Testaremos indiretamente através de um registro concreto
        $registro0000 = new Registro0000('12345678901234');

        $this->assertEquals('0000', $registro0000->getTipoRegistro());
        $this->assertNotEmpty($registro0000->converterParaLinhaTxt());
        $this->assertTrue($registro0000->isValid());
    }

    public function test_registro_0000_creation_and_validation()
    {
        $registro = new Registro0000('12345678901234');

        $this->assertEquals('0000', $registro->getTipoRegistro());
        $this->assertEquals('12345678901234', $registro->getInscricaoEmpresa());
        $this->assertTrue($registro->isValid());

        // Teste de formatação
        $linha = $registro->converterParaLinhaTxt();
        $this->assertStringStartsWith('0000|', $linha);
        $this->assertStringContainsString('12345678901234', $linha);
    }

    public function test_registro_0100_creation_and_validation()
    {
        $registro = new Registro0100('PROD001', 'Produto de Teste');
        $registro->setCodigoNcm('12345678');
        $registro->setCodigoBarras('1234567890123');
        $registro->setValorUnitario(10.50);

        $this->assertEquals('0100', $registro->getTipoRegistro());
        $this->assertEquals('PROD001', $registro->getCodigoProduto());
        $this->assertEquals('Produto de Teste', $registro->getDescricaoProduto());
        $this->assertTrue($registro->isValid());

        // Teste de formatação
        $linha = $registro->converterParaLinhaTxt();
        $this->assertStringStartsWith('0100|', $linha);
        $this->assertStringContainsString('PROD001', $linha);
        $this->assertStringContainsString('Produto de Teste', $linha);
    }

    public function test_registro_1000_creation_and_validation()
    {
        $dataEntrada = new \DateTime('2023-01-01');
        $dataEmissao = new \DateTime('2023-01-01');

        $registro = new Registro1000(
            '55',
            '12345678901234',
            '5101',
            12345,
            $dataEntrada,
            $dataEmissao,
            100.50,
            100.50,
            '3550308',
            0
        );

        $this->assertEquals('1000', $registro->getTipoRegistro());
        $this->assertEquals('55', $registro->getCodigoEspecie());
        $this->assertEquals('12345678901234', $registro->getInscricaoFornecedor());
        $this->assertTrue($registro->isValid());

        // Teste de formatação
        $linha = $registro->converterParaLinhaTxt();
        $this->assertStringStartsWith('1000|', $linha);
        $this->assertStringContainsString('55', $linha);
        $this->assertStringContainsString('12345678901234', $linha);
    }

    public function test_registro_factory_creates_correct_instances()
    {
        $registro0000 = RegistroFactory::criarRegistro('0000', ['inscricao_empresa' => '12345678901234']);
        $this->assertInstanceOf(Registro0000::class, $registro0000);
        $this->assertEquals('0000', $registro0000->getTipoRegistro());

        $registro0100 = RegistroFactory::criarRegistro('0100', [
            'codigo_produto' => 'PROD001',
            'descricao_produto' => 'Produto Teste',
        ]);
        $this->assertInstanceOf(Registro0100::class, $registro0100);
        $this->assertEquals('0100', $registro0100->getTipoRegistro());

        $dataEntrada = new \DateTime('2023-01-01');
        $dataEmissao = new \DateTime('2023-01-01');

        $registro1000 = RegistroFactory::criarRegistro('1000', [
            'codigo_especie' => '55',
            'inscricao_fornecedor' => '12345678901234',
            'cfop' => '5101',
            'numero_documento' => 12345,
            'data_entrada' => $dataEntrada,
            'data_emissao' => $dataEmissao,
            'valor_contabil' => 100.50,
            'valor_produtos' => 100.50,
            'municipio_origem' => '3550308',
            'situacao_nota' => 0,
        ]);
        $this->assertInstanceOf(Registro1000::class, $registro1000);
        $this->assertEquals('1000', $registro1000->getTipoRegistro());
    }

    public function test_dominio_sistemas_service_generates_txt_content()
    {
        $service = new DominioSistemasService;

        // Criação de registros de teste
        $registro0000 = new Registro0000('12345678901234');
        $registro0100 = new Registro0100('PROD001', 'Produto Teste');
        $registro0100->setCodigoNcm('12345678');
        $registro0100->setValorUnitario(10.50);

        $registros = [$registro0000, $registro0100];

        $conteudo = $service->gerarConteudoTxt($registros);

        $this->assertStringContainsString('0000|12345678901234', $conteudo);
        $this->assertStringContainsString('0100|PROD001|Produto Teste', $conteudo);
    }

    public function test_registro_factory_throws_exception_for_invalid_type()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Tipo de registro desconhecido:');

        RegistroFactory::criarRegistro('9999', []);
    }
}
