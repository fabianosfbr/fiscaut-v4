<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Inadimplentes</title>
    <style>
        body { font-family: sans-serif; font-size: 10px; margin: 0; padding: 20px; }
        .header { margin-bottom: 20px; }
        .header h1 { font-size: 16px; margin: 0 0 5px; }
        .header p { font-size: 10px; margin: 0; color: #555; }
        .issuer-name { font-size: 10px; text-transform: uppercase; margin-bottom: 5px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 10px; }
        th, td { padding: 4px; border-bottom: 1px solid #ddd; }
        th { text-align: left; font-weight: normal; color: #555; border-bottom: 2px solid #555; border-top: 1px solid #555; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .unit-header { font-weight: bold; color: #666; font-size: 10px; text-transform: uppercase; margin-top: 15px; margin-bottom: 5px; }
        .total-row td { font-weight: bold; border-top: 1px solid #000; border-bottom: 2px solid #000; }
        .grand-total td { font-weight: bold; border-top: 2px solid #000; font-size: 11px; }
    </style>
</head>
<body>

    <div class="header">
        <div class="issuer-name">{{ $issuerName }} ({{ $idCondominio }})</div>
        <h1>Inadimplentes</h1>
        <p>Valores atualizados até {{ now()->format('d/m/Y') }}</p>
    </div>

    @php
        $grandTotalPrincipal = 0;
        $grandTotalJuros = 0;
        $grandTotalMulta = 0;
        $grandTotalAtualiz = 0;
        $grandTotalHonorarios = 0;
        $grandTotalTotal = 0;
    @endphp

    @foreach($records as $record)
        @php
            $recebimentos = $record['recebimento'] ?? [];
            if(empty($recebimentos)) continue;

            $unitTotalPrincipal = 0;
            $unitTotalJuros = 0;
            $unitTotalMulta = 0;
            $unitTotalAtualiz = 0;
            $unitTotalHonorarios = 0;
            $unitTotalTotal = 0;
        @endphp

        <div class="unit-header">
            {{ data_get($record, 'st_unidade_uni') }} {{ data_get($record, 'st_bloco_uni') }} - {{ data_get($record, 'st_sacado_uni') }}
        </div>

        <table>
            <thead>
                <tr>
                    <th class="text-center">Vencimento</th>
                    <th class="text-center">Compet.</th>
                    <th class="text-center">Atraso</th>
                    <th class="text-center">Código</th>
                    <th class="text-right">Principal</th>
                    <th class="text-right">Juros</th>
                    <th class="text-right">Multa</th>
                    <th class="text-right">Atualiz.</th>
                    <th class="text-right">Honorários</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($recebimentos as $recb)
                    @php
                        $principal = (float) data_get($recb, 'vl_emitido_recb', 0);
                        $juros = (float) data_get($recb, 'encargos.0.detalhes.juros', 0);
                        $multa = (float) data_get($recb, 'encargos.0.detalhes.multa', 0);
                        $atualiz = (float) data_get($recb, 'encargos.0.detalhes.atualizacaomonetaria', 0);
                        $honorarios = (float) data_get($recb, 'encargos.0.detalhes.honorarios', 0);
                        $total = (float) data_get($recb, 'encargos.0.valorcorrigido', 0);

                        $unitTotalPrincipal += $principal;
                        $unitTotalJuros += $juros;
                        $unitTotalMulta += $multa;
                        $unitTotalAtualiz += $atualiz;
                        $unitTotalHonorarios += $honorarios;
                        $unitTotalTotal += $total;

                        try {
                            $vencimento = \Illuminate\Support\Carbon::parse(data_get($recb, 'dt_vencimento_recb'))->format('d/m/y');
                        } catch (\Exception $e) {
                            $vencimento = data_get($recb, 'dt_vencimento_recb');
                        }

                        try {
                            $competencia = \Illuminate\Support\Carbon::parse(data_get($recb, 'dt_competencia_recb'))->format('m/Y');
                        } catch (\Exception $e) {
                            $competencia = data_get($recb, 'dt_competencia_recb');
                        }
                    @endphp
                    <tr>
                        <td class="text-center">{{ $vencimento }}</td>
                        <td class="text-center">{{ $competencia }}</td>
                        <td class="text-center">{{ data_get($recb, 'encargos.0.diasatraso', 0) }}</td>
                        <td class="text-center">{{ data_get($recb, 'id_recebimento_recb') }}</td>
                        <td class="text-right">{{ number_format($principal, 2, ',', '.') }}</td>
                        <td class="text-right">{{ number_format($juros, 2, ',', '.') }}</td>
                        <td class="text-right">{{ number_format($multa, 2, ',', '.') }}</td>
                        <td class="text-right">{{ number_format($atualiz, 2, ',', '.') }}</td>
                        <td class="text-right">{{ number_format($honorarios, 2, ',', '.') }}</td>
                        <td class="text-right">{{ number_format($total, 2, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tbody>
                <tr class="total-row">
                    <td colspan="4" class="text-center">Total</td>
                    <td class="text-right">{{ number_format($unitTotalPrincipal, 2, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($unitTotalJuros, 2, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($unitTotalMulta, 2, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($unitTotalAtualiz, 2, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($unitTotalHonorarios, 2, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($unitTotalTotal, 2, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>

        @php
            $grandTotalPrincipal += $unitTotalPrincipal;
            $grandTotalJuros += $unitTotalJuros;
            $grandTotalMulta += $unitTotalMulta;
            $grandTotalAtualiz += $unitTotalAtualiz;
            $grandTotalHonorarios += $unitTotalHonorarios;
            $grandTotalTotal += $unitTotalTotal;
        @endphp
    @endforeach

    <table style="margin-top: 30px; border-top: 2px solid #000;">
        <tr class="grand-total">
            <td colspan="4" class="text-center" style="border: none;">TOTAL GERAL</td>
            <td class="text-right" style="border: none;">{{ number_format($grandTotalPrincipal, 2, ',', '.') }}</td>
            <td class="text-right" style="border: none;">{{ number_format($grandTotalJuros, 2, ',', '.') }}</td>
            <td class="text-right" style="border: none;">{{ number_format($grandTotalMulta, 2, ',', '.') }}</td>
            <td class="text-right" style="border: none;">{{ number_format($grandTotalAtualiz, 2, ',', '.') }}</td>
            <td class="text-right" style="border: none;">{{ number_format($grandTotalHonorarios, 2, ',', '.') }}</td>
            <td class="text-right" style="border: none;">{{ number_format($grandTotalTotal, 2, ',', '.') }}</td>
        </tr>
    </table>

</body>
</html>