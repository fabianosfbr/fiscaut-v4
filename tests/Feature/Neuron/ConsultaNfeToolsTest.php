<?php

namespace Tests\Feature\Neuron;

use App\Models\Issuer;
use App\Models\NotaFiscalEletronica;
use App\Models\User;
use App\Neuron\Tools\ConsultaNfeEntradaTool;
use App\Neuron\Tools\ConsultaNfeSaidaTool;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ConsultaNfeToolsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (! Schema::hasTable('nfes')) {
            Schema::create('nfes', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id')->nullable();
                $table->string('chave')->nullable();
                $table->integer('nNF')->nullable();
                $table->string('serie')->nullable();
                $table->dateTime('data_emissao')->nullable();
                $table->dateTime('data_entrada')->nullable();
                $table->string('emitente_cnpj')->nullable();
                $table->string('emitente_razao_social')->nullable();
                $table->string('destinatario_cnpj')->nullable();
                $table->string('destinatario_razao_social')->nullable();
                $table->string('tpNf')->nullable();
                $table->integer('status_nota')->nullable();
                $table->decimal('vNfe', 14, 2)->nullable();
                $table->json('cfops')->nullable();
                $table->integer('num_produtos')->nullable();
                $table->decimal('vICMSUFDest', 14, 2)->nullable();
                $table->longText('xml')->nullable();
                $table->timestamps();
            });
        }
    }

    public function test_consulta_nfe_entrada_filtra_por_tipo_entrada(): void
    {
        $issuer = new Issuer();
        $issuer->id = 1;
        $issuer->tenant_id = 10;
        $issuer->cnpj = '11111111000191';

        $user = new User();
        $user->id = 99;
        $user->issuer_id = $issuer->id;
        $user->setRelation('currentIssuer', $issuer);

        Auth::login($user);

        NotaFiscalEletronica::query()->create([
            'tenant_id' => $issuer->tenant_id,
            'chave' => 'A',
            'nNF' => 1,
            'serie' => '1',
            'data_emissao' => '2026-02-10 10:00:00',
            'emitente_cnpj' => '22222222000191',
            'emitente_razao_social' => 'Fornecedor X',
            'destinatario_cnpj' => $issuer->cnpj,
            'destinatario_razao_social' => 'Empresa Y',
            'tpNf' => '1',
            'status_nota' => 100,
            'vNfe' => 10,
            'cfops' => ['1102'],
            'num_produtos' => 1,
            'vICMSUFDest' => 0,
        ]);

        NotaFiscalEletronica::query()->create([
            'tenant_id' => $issuer->tenant_id,
            'chave' => 'B',
            'nNF' => 2,
            'serie' => '1',
            'data_emissao' => '2026-02-10 11:00:00',
            'emitente_cnpj' => $issuer->cnpj,
            'emitente_razao_social' => 'Empresa Y',
            'destinatario_cnpj' => '33333333000191',
            'destinatario_razao_social' => 'Cliente Z',
            'tpNf' => '0',
            'status_nota' => 100,
            'vNfe' => 20,
            'cfops' => ['5102'],
            'num_produtos' => 2,
            'vICMSUFDest' => 0,
        ]);

        NotaFiscalEletronica::query()->create([
            'tenant_id' => $issuer->tenant_id,
            'chave' => 'C',
            'nNF' => 3,
            'serie' => '1',
            'data_emissao' => '2026-02-10 12:00:00',
            'emitente_cnpj' => '44444444000191',
            'emitente_razao_social' => 'Remetente W',
            'destinatario_cnpj' => $issuer->cnpj,
            'destinatario_razao_social' => 'Empresa Y',
            'tpNf' => '0',
            'status_nota' => 100,
            'vNfe' => 30,
            'cfops' => ['1556'],
            'num_produtos' => 3,
            'vICMSUFDest' => 0,
        ]);

        NotaFiscalEletronica::query()->create([
            'tenant_id' => 999,
            'chave' => 'D',
            'nNF' => 4,
            'serie' => '1',
            'data_emissao' => '2026-02-10 13:00:00',
            'emitente_cnpj' => '55555555000191',
            'destinatario_cnpj' => $issuer->cnpj,
            'tpNf' => '1',
        ]);

        $tool = new ConsultaNfeEntradaTool();

        $result = $tool(
            'terceiros',
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            false,
            10,
        );

        $this->assertSame(1, $result['count']);
        $this->assertCount(1, $result['items']);
        $this->assertSame('A', $result['items'][0]['chave']);
        $this->assertSame('terceiros', $result['items'][0]['tipo_entrada']);
    }

    public function test_consulta_nfe_saida_restringe_por_emitente_do_issuer(): void
    {
        $issuer = new Issuer();
        $issuer->id = 2;
        $issuer->tenant_id = 20;
        $issuer->cnpj = '99999999000191';

        $user = new User();
        $user->id = 100;
        $user->issuer_id = $issuer->id;
        $user->setRelation('currentIssuer', $issuer);

        Auth::login($user);

        NotaFiscalEletronica::query()->create([
            'tenant_id' => $issuer->tenant_id,
            'chave' => 'S1',
            'nNF' => 10,
            'serie' => '1',
            'data_emissao' => '2026-02-10 10:00:00',
            'emitente_cnpj' => $issuer->cnpj,
            'destinatario_cnpj' => '11111111000191',
            'tpNf' => '1',
            'status_nota' => 100,
            'vNfe' => 100,
            'cfops' => ['5102'],
        ]);

        NotaFiscalEletronica::query()->create([
            'tenant_id' => $issuer->tenant_id,
            'chave' => 'S2',
            'nNF' => 11,
            'serie' => '1',
            'data_emissao' => '2026-02-10 11:00:00',
            'emitente_cnpj' => '22222222000191',
            'destinatario_cnpj' => '11111111000191',
            'tpNf' => '1',
            'status_nota' => 100,
            'vNfe' => 110,
            'cfops' => ['5102'],
        ]);

        NotaFiscalEletronica::query()->create([
            'tenant_id' => 999,
            'chave' => 'S3',
            'emitente_cnpj' => $issuer->cnpj,
        ]);

        $tool = new ConsultaNfeSaidaTool();

        $result = $tool(
            null,
            null,
            null,
            null,
            null,
            null,
            false,
            50,
        );

        $this->assertSame(1, $result['count']);
        $this->assertCount(1, $result['items']);
        $this->assertSame('S1', $result['items'][0]['chave']);
        $this->assertSame('saida', $result['items'][0]['tipo_documento']);
    }

    public function test_consulta_nfe_entrada_so_inclui_itens_em_modo_detalhe(): void
    {
        $issuer = new Issuer();
        $issuer->id = 3;
        $issuer->tenant_id = 30;
        $issuer->cnpj = '12345678000199';

        $user = new User();
        $user->id = 101;
        $user->issuer_id = $issuer->id;
        $user->setRelation('currentIssuer', $issuer);

        Auth::login($user);

        $xml = file_get_contents(base_path('xml-nfe.xml'));
        $this->assertNotFalse($xml);

        NotaFiscalEletronica::query()->create([
            'tenant_id' => $issuer->tenant_id,
            'chave' => 'DET1',
            'nNF' => 99,
            'serie' => '1',
            'data_emissao' => '2026-02-10 10:00:00',
            'emitente_cnpj' => '22222222000191',
            'emitente_razao_social' => 'Fornecedor X',
            'destinatario_cnpj' => $issuer->cnpj,
            'destinatario_razao_social' => 'Empresa Y',
            'tpNf' => '1',
            'status_nota' => 100,
            'vNfe' => 10,
            'cfops' => ['1102'],
            'num_produtos' => 28,
            'xml' => gzcompress($xml),
        ]);

        $tool = new ConsultaNfeEntradaTool();

        $list = $tool(
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            true,
            10,
        );

        $this->assertSame(1, $list['count']);
        $this->assertArrayNotHasKey('produtos', $list['items'][0]);
        $this->assertNotEmpty($list['warnings']);

        $detail = $tool(
            null,
            'DET1',
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            true,
            10,
        );

        $this->assertSame(1, $detail['count']);
        $this->assertArrayHasKey('produtos', $detail['items'][0]);
        $this->assertIsArray($detail['items'][0]['produtos']);
    }
}
