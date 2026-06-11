<?php

namespace Database\Seeders;

use App\Models\NcmRestricao;
use Illuminate\Database\Seeder;

class NcmRestricaoSeeder extends Seeder
{
    private array $data = [
        // ===== GRUPO 1 — ALIMENTOS CESTA BÁSICA (alíquota zero) =====
        [
            'grupo' => '110', 'descricao' => 'Leite e creme de leite',
            'tipo' => 'ALIQUOTA_ZERO', 'tipo_match' => 'prefixo',
            'valor_match' => ['0401', '0402'],
            'fundamento' => 'Lei 10.865/2004, art. 28, III',
            'setores' => ['alimentos'],
        ],
        [
            'grupo' => '111', 'descricao' => 'Queijos e requeijao',
            'tipo' => 'ALIQUOTA_ZERO', 'tipo_match' => 'prefixo',
            'valor_match' => ['0406'],
            'fundamento' => 'Lei 10.865/2004, art. 28, III',
            'setores' => ['alimentos'],
        ],
        [
            'grupo' => '113', 'descricao' => 'Farinha de trigo',
            'tipo' => 'ALIQUOTA_ZERO', 'tipo_match' => 'exato',
            'valor_match' => ['11010010'],
            'fundamento' => 'Lei 10.925/2004, art. 1o, XIV',
            'setores' => ['alimentos'],
        ],
        [
            'grupo' => '114', 'descricao' => 'Trigo em grao',
            'tipo' => 'ALIQUOTA_ZERO', 'tipo_match' => 'prefixo',
            'valor_match' => ['1001'],
            'fundamento' => 'Lei 10.925/2004, art. 1o, XV',
            'setores' => ['alimentos'],
        ],
        [
            'grupo' => '115', 'descricao' => 'Pre-misturas e pao comum',
            'tipo' => 'ALIQUOTA_ZERO', 'tipo_match' => 'exato',
            'valor_match' => ['19012000', '19059090'],
            'fundamento' => 'Lei 10.925/2004, art. 1o, XVI',
            'setores' => ['alimentos'],
            'possui_ex' => true,
        ],
        [
            'grupo' => '116', 'descricao' => 'Horticolas e frutas',
            'tipo' => 'ALIQUOTA_ZERO', 'tipo_match' => 'capitulo',
            'valor_match' => ['07', '08'],
            'fundamento' => 'Lei 10.865/2004, art. 28, III',
            'setores' => ['alimentos'],
        ],
        [
            'grupo' => '117', 'descricao' => 'Ovos',
            'tipo' => 'ALIQUOTA_ZERO', 'tipo_match' => 'prefixo',
            'valor_match' => ['0407'],
            'fundamento' => 'Lei 10.865/2004, art. 28, III',
            'setores' => ['alimentos'],
        ],
        [
            'grupo' => '119', 'descricao' => 'Massas alimenticias',
            'tipo' => 'ALIQUOTA_ZERO', 'tipo_match' => 'exato',
            'valor_match' => ['19021100', '19021900', '19022000', '19023000'],
            'fundamento' => 'Lei 10.925/2004, art. 1o, XVIII',
            'setores' => ['alimentos'],
        ],
        [
            'grupo' => '121', 'descricao' => 'Carnes e produtos de origem animal',
            'tipo' => 'ALIQUOTA_ZERO', 'tipo_match' => 'prefixo',
            'valor_match' => [
                '0201', '0202', '02061000', '02062', '02102000',
                '05069000', '05100010', '1502101',
                '0203', '02063000', '02064', '0207', '0209',
                '02101', '02109900', '0204', '02068000',
            ],
            'fundamento' => 'MP 609/2013, art. 1o',
            'setores' => ['alimentos'],
        ],
        [
            'grupo' => '122', 'descricao' => 'Peixes',
            'tipo' => 'ALIQUOTA_ZERO', 'tipo_match' => 'prefixo',
            'valor_match' => ['0302', '0303', '0304'],
            'excluir_ncm' => ['03029000'],
            'fundamento' => 'MP 609/2013, art. 1o',
            'setores' => ['alimentos'],
        ],
        [
            'grupo' => '123', 'descricao' => 'Cafe',
            'tipo' => 'ALIQUOTA_ZERO', 'tipo_match' => 'prefixo',
            'valor_match' => ['0901', '21011'],
            'fundamento' => 'MP 609/2013, art. 1o',
            'setores' => ['alimentos'],
        ],
        [
            'grupo' => '124', 'descricao' => 'Acucar',
            'tipo' => 'ALIQUOTA_ZERO', 'tipo_match' => 'exato',
            'valor_match' => ['17011400', '17019900'],
            'fundamento' => 'MP 609/2013, art. 1o',
            'setores' => ['alimentos'],
        ],
        [
            'grupo' => '125', 'descricao' => 'Oleos vegetais',
            'tipo' => 'ALIQUOTA_ZERO', 'tipo_match' => 'faixa_prefixo',
            'valor_match' => [['1508', '1514'], '1507'],
            'fundamento' => 'MP 609/2013, art. 1o',
            'setores' => ['alimentos'],
        ],
        [
            'grupo' => '126', 'descricao' => 'Manteiga',
            'tipo' => 'ALIQUOTA_ZERO', 'tipo_match' => 'exato',
            'valor_match' => ['04051000'],
            'fundamento' => 'MP 609/2013, art. 1o',
            'setores' => ['alimentos'],
        ],
        [
            'grupo' => '127', 'descricao' => 'Margarina',
            'tipo' => 'ALIQUOTA_ZERO', 'tipo_match' => 'exato',
            'valor_match' => ['15171000'],
            'fundamento' => 'MP 609/2013, art. 1o',
            'setores' => ['alimentos'],
        ],

        // ===== GRUPO 2 — MONOFÁSICOS =====
        [
            'grupo' => 'MON-001', 'descricao' => 'Combustiveis derivados de petroleo',
            'tipo' => 'MONOFASICO', 'tipo_match' => 'prefixo',
            'valor_match' => ['2710'],
            'fundamento' => 'Lei 9.718/1998, art. 4o; Lei 10.865/2004',
            'setores' => ['combustiveis', 'industria', 'geral'],
            'obs' => 'Gasolina, diesel, querosene, oleos lubrificantes.',
        ],
        [
            'grupo' => 'MON-002', 'descricao' => 'Gas natural e GLP',
            'tipo' => 'MONOFASICO', 'tipo_match' => 'prefixo',
            'valor_match' => ['2711'],
            'fundamento' => 'Lei 9.718/1998, art. 4o',
            'setores' => ['combustiveis', 'industria', 'geral'],
        ],
        [
            'grupo' => 'MON-003', 'descricao' => 'Alcool etilico combustivel',
            'tipo' => 'MONOFASICO', 'tipo_match' => 'exato',
            'valor_match' => ['22072000', '22071000'],
            'fundamento' => 'Lei 9.718/1998, art. 5o',
            'setores' => ['combustiveis', 'geral'],
        ],
        [
            'grupo' => 'MON-004', 'descricao' => 'Medicamentos',
            'tipo' => 'MONOFASICO', 'tipo_match' => 'prefixo',
            'valor_match' => ['3003', '3004'],
            'fundamento' => 'Lei 10.147/2000, art. 1o',
            'setores' => ['farma', 'geral'],
            'obs' => 'Medicamentos e produtos farmaceuticos — monofasico.',
        ],
        [
            'grupo' => 'MON-005', 'descricao' => 'Bebidas frias (refrigerantes, cervejas, aguas)',
            'tipo' => 'MONOFASICO', 'tipo_match' => 'prefixo',
            'valor_match' => ['2201', '2202', '2203', '2204', '2205', '2206', '2208'],
            'fundamento' => 'Lei 10.833/2003, art. 58-A a 58-V',
            'setores' => ['alimentos', 'bebidas', 'geral'],
        ],
        [
            'grupo' => 'MON-006', 'descricao' => 'Autopecas',
            'tipo' => 'MONOFASICO', 'tipo_match' => 'prefixo',
            'valor_match' => ['8407', '8408', '8409', '8413', '8421', '8431', '8483', '8484', '8485', '8708', '8714'],
            'fundamento' => 'Lei 10.485/2002, art. 3o',
            'setores' => ['automovel', 'industria', 'geral'],
            'obs' => 'Autopecas — lista nao exaustiva; conferir TIPI completa.',
        ],
        [
            'grupo' => 'MON-007', 'descricao' => 'Pneus e camaras de ar',
            'tipo' => 'MONOFASICO', 'tipo_match' => 'prefixo',
            'valor_match' => ['4011', '4012', '4013'],
            'fundamento' => 'Lei 10.485/2002',
            'setores' => ['automovel', 'industria', 'geral'],
        ],
        [
            'grupo' => 'MON-008', 'descricao' => 'Embarcacoes',
            'tipo' => 'MONOFASICO', 'tipo_match' => 'prefixo',
            'valor_match' => ['8901', '8902', '8903', '8904', '8905'],
            'fundamento' => 'Lei 10.865/2004, art. 28',
            'setores' => ['naval', 'geral'],
        ],

        // ===== GRUPO 3 — SUSPENSÃO =====
        [
            'grupo' => 'SUP-ZFM', 'descricao' => 'Produtos destinados a ZFM',
            'tipo' => 'SUSPENSAO', 'tipo_match' => 'capitulo',
            'valor_match' => [],
            'fundamento' => 'Lei 10.637/2002, art. 2o, §3o; Decreto-lei 288/1967',
            'setores' => ['zfm'],
            'obs' => 'Suspensao definida pelo CFOP e UF destino (AM), nao pelo NCM.',
        ],
        [
            'grupo' => 'SUP-DRW', 'descricao' => 'Insumos importados com drawback',
            'tipo' => 'SUSPENSAO', 'tipo_match' => 'capitulo',
            'valor_match' => [],
            'fundamento' => 'Lei 11.945/2009, art. 12',
            'setores' => ['importacao', 'industria'],
            'obs' => 'Suspensao por regime aduaneiro especial.',
        ],

        // ===== GRUPO 4 — ISENÇÃO =====
        [
            'grupo' => 'ISE-001', 'descricao' => 'Livros jornais e periodicos',
            'tipo' => 'ISENCAO', 'tipo_match' => 'prefixo',
            'valor_match' => ['4901', '4902', '4903', '4904', '4905'],
            'fundamento' => 'CF/1988, art. 150, VI, d; Lei 10.865/2004',
            'setores' => ['editorial', 'geral'],
        ],
        [
            'grupo' => 'ISE-002', 'descricao' => 'Produtos quimicos e farmaceuticos prioritarios',
            'tipo' => 'ISENCAO', 'tipo_match' => 'prefixo',
            'valor_match' => ['2936', '2937', '2941'],
            'fundamento' => 'Lei 10.865/2004, art. 28, II',
            'setores' => ['farma', 'quimica', 'geral'],
            'obs' => 'Vitaminas, hormonios, antibioticos — isencao especifica.',
        ],
    ];

    public function run(): void
    {
        foreach ($this->data as $row) {
            NcmRestricao::create($row);
        }

        $this->command->info('NcmRestricaoSeeder: '.count($this->data).' registros inseridos.');
    }
}
