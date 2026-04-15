<div class="overflow-x-auto">
    <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
        <thead class="text-xs text-gray-700 uppercase border-b border-t border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
            <tr>
                <th class="px-4 py-2 font-bold text-center">Vencimento</th>
                <th class="px-4 py-2 font-bold text-center">Compet.</th>
                <th class="px-4 py-2 font-bold text-center">Atraso</th>
                <th class="px-4 py-2 font-bold text-center">Código</th>
                <th class="px-4 py-2 font-bold text-right">Principal</th>
                <th class="px-4 py-2 font-bold text-right">Juros</th>
                <th class="px-4 py-2 font-bold text-right">Multa</th>
                <th class="px-4 py-2 font-bold text-right">Atualiz.</th>
                <th class="px-4 py-2 font-bold text-right">Honorários</th>
                <th class="px-4 py-2 font-bold text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalPrincipal = 0;
                $totalJuros = 0;
                $totalMulta = 0;
                $totalAtualiz = 0;
                $totalHonorarios = 0;
                $totalTotal = 0;
            @endphp
            @foreach($recebimentos as $recb)
                @php
                    $principal = (float) data_get($recb, 'vl_emitido_recb', 0);
                    $juros = (float) data_get($recb, 'encargos.0.detalhes.juros', 0);
                    $multa = (float) data_get($recb, 'encargos.0.detalhes.multa', 0);
                    $atualiz = (float) data_get($recb, 'encargos.0.detalhes.atualizacaomonetaria', 0);
                    $honorarios = (float) data_get($recb, 'encargos.0.detalhes.honorarios', 0);
                    $total = (float) data_get($recb, 'encargos.0.valorcorrigido', 0);

                    $totalPrincipal += $principal;
                    $totalJuros += $juros;
                    $totalMulta += $multa;
                    $totalAtualiz += $atualiz;
                    $totalHonorarios += $honorarios;
                    $totalTotal += $total;

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
                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                    <td class="px-4 py-2 text-center">{{ $vencimento }}</td>
                    <td class="px-4 py-2 text-center">{{ $competencia }}</td>
                    <td class="px-4 py-2 text-center">{{ data_get($recb, 'encargos.0.diasatraso', 0) }}</td>
                    <td class="px-4 py-2 text-center">{{ data_get($recb, 'id_recebimento_recb') }}</td>
                    <td class="px-4 py-2 text-right">{{ number_format($principal, 2, ',', '.') }}</td>
                    <td class="px-4 py-2 text-right">{{ number_format($juros, 2, ',', '.') }}</td>
                    <td class="px-4 py-2 text-right">{{ number_format($multa, 2, ',', '.') }}</td>
                    <td class="px-4 py-2 text-right">{{ number_format($atualiz, 2, ',', '.') }}</td>
                    <td class="px-4 py-2 text-right">{{ number_format($honorarios, 2, ',', '.') }}</td>
                    <td class="px-4 py-2 text-right">{{ number_format($total, 2, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="font-bold text-gray-900 dark:text-white border-t border-b border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700">
                <td colspan="4" class="px-4 py-2 text-center">Total</td>
                <td class="px-4 py-2 text-right">{{ number_format($totalPrincipal, 2, ',', '.') }}</td>
                <td class="px-4 py-2 text-right">{{ number_format($totalJuros, 2, ',', '.') }}</td>
                <td class="px-4 py-2 text-right">{{ number_format($totalMulta, 2, ',', '.') }}</td>
                <td class="px-4 py-2 text-right">{{ number_format($totalAtualiz, 2, ',', '.') }}</td>
                <td class="px-4 py-2 text-right">{{ number_format($totalHonorarios, 2, ',', '.') }}</td>
                <td class="px-4 py-2 text-right">{{ number_format($totalTotal, 2, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>
</div>