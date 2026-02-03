<x-filament-panels::page>
    <div class="relative overflow-x-auto">
        @php
        $total = array_reduce($faturamento, function($total, $valor){ return $total += $valor['faturamento'] ;});
        $totalNfse = array_reduce($faturamento, function($total, $valor){ return $total += $valor['faturamento-nfse'] ;});
        $totalIcms = array_reduce($faturamento, function($total, $valor){ return $total += $valor['icms'] ;});
        $totalIcmsST = array_reduce($faturamento, function($total, $valor){ return $total += $valor['icmsST'] ;});
        $totalIPI = array_reduce($faturamento, function($total, $valor){ return $total += $valor['ipi'] ;});
        $totalPIS = array_reduce($faturamento, function($total, $valor){ return $total += $valor['pis'] ;});
        $totalCOFINS = array_reduce($faturamento, function($total, $valor){ return $total += $valor['cofins'] ;});
        $totalCPRB = array_reduce($faturamento, function($total, $valor){ return $total += $valor['cprb'] ;});
        $totalCSLL = array_reduce($faturamento, function($total, $valor){ return $total += $valor['csll'] ;});
        $totalIRPJ = array_reduce($faturamento, function($total, $valor){ return $total += $valor['irpj'] ;});
        $totalFaturamentoLiquido = array_reduce($faturamento, function($total, $valor){ return $total += $valor['faturamentoLiquido'] ;});

        @endphp

        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                <tr>
                    <th scope="col" class="px-3 py-3 whitespace-nowrap">
                        Data Ref.
                    </th>
                    <th scope="col" class="px-3 py-3">
                        <div class="whitespace-nowrap">Faturamento</div>
                        <div class="text-slate-500 text-xs whitespace-nowrap">NF-e / e-SAT / CF-e / CT-e</div>
                    </th>
                    <th scope="col" class="px-3 py-3 whitespace-nowrap">

                    </th>
                    <th scope="col" class="px-3 py-3 ">
                        <div class="whitespace-nowrap">Faturamento</div>
                        <div class="text-slate-500 text-xs whitespace-nowrap">NFS-e</div>
                    </th>
                    <th scope="col" class="px-3 py-3">

                    </th>
                    <th scope="col" class="px-3 py-3 text-center">
                        ICMS
                    </th>
                    <th scope="col" class="px-3 py-3">

                    </th>
                    <th scope="col" class="px-3 py-3 text-center">
                        ICMS ST
                    </th>
                    <th scope="col" class="px-3 py-3">

                    </th>
                    <th scope="col" class="px-3 py-3 text-center">
                        IPI
                    </th>
                    <th scope="col" class="px-3 py-3">

                    </th>
                    <th scope="col" class="px-3 py-3 text-center">
                        ISSQN
                    </th>
                    <th scope="col" class="px-3 py-3">

                    </th>
                    <th scope="col" class="px-3 py-3 text-center">
                        PIS
                    </th>
                    <th scope="col" class="px-3 py-3">

                    </th>
                    <th scope="col" class="px-3 py-3 text-center">
                        COFINS
                    </th>
                    <th scope="col" class="px-3 py-3">

                    </th>
                    <th scope="col" class="px-3 py-3 text-center">
                        <div class="whitespace-nowrap">Simples</div>
                        <div class="whitespace-nowrap">Nacional</div>
                    </th>
                    <th scope="col" class="px-3 py-3">

                    </th>
                    <th scope="col" class="px-3 py-3 text-center">
                        CPRB
                    </th>
                    <th scope="col" class="px-3 py-3">

                    </th>
                    <th scope="col" class="px-3 py-3 text-center">
                        CSLL L.P.
                    </th>
                    <th scope="col" class="px-3 py-3">

                    </th>
                    <th scope="col" class="px-3 py-3 text-center">
                        IRPJ L.P.
                    </th>
                    <th scope="col" class="px-3 py-3">

                    </th>
                    <th scope="col" class="px-3 py-3">
                        <div class="whitespace-nowrap">Faturamento</div>
                        <div class="whitespace-nowrap">Líquido</div>
                    </th>

                </tr>
            </thead>
            <tbody>
                @foreach ($faturamento as $index => $data )
                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                    @php
                    $faturamento = $data['faturamento'];
                    $percentualFaturamento = $total > 0 ? $data['faturamento']/$total : 0;
                    $icms = $data['icms'];
                    $percentualICMS = $totalIcms > 0 ? $icms/$totalIcms : 0;
                    $icmsST = $data['icmsST'];
                    $percentualICMSST = $totalIcmsST > 0 ? $icmsST/$totalIcmsST : 0;
                    $ipi = $data['ipi'];
                    $percentualIPI = $totalIPI > 0 ? $ipi/$totalIPI : 0;
                    $pis = $data['pis'];
                    $percentualPIS = $totalPIS > 0 ? $pis/$totalPIS : 0;
                    $cofins = $data['cofins'];
                    $percentualCOFINS = $totalCOFINS > 0 ? $cofins/$totalCOFINS : 0;
                    $cprb = $data['cprb'];
                    $percentualCPRB = $totalCPRB > 0 ? $cprb/$totalCPRB : 0;
                    $csll = $data['csll'];
                    $percentualCSLL = $totalCSLL > 0 ? $csll/$totalCSLL : 0;
                    $irpj = $data['irpj'];
                    $percentualIRPJ = $totalIRPJ > 0 ? $irpj/$totalIRPJ : 0;
                    $faturamentoLiquido = $faturamento - $icms- $ipi - $icmsST - $pis - $cofins - $cprb - $csll - $irpj;
                    @endphp
                    <th scope="row" class="px-3 py-2 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                        {{$index}}
                    </th>
                    <td class="px-3 py-2 whitespace-nowrap">
                        {{formatar_moeda($faturamento)}}
                    </td>
                    <td class="px-3 py-2 whitespace-nowrap">
                        {{ $percentualFaturamento }}
                    </td>
                    <td class="px-3 py-2 whitespace-nowrap">
                        R$ 0,00
                    </td>
                    <td class="px-3 py-2 whitespace-nowrap">
                        0,00 %
                    </td>
                    <td class="px-3 py-2 whitespace-nowrap">
                        {{formatar_moeda($icms)}}
                    </td>
                    <td class="px-3 py-2 whitespace-nowrap">
                        {{ $percentualICMS }}
                    </td>
                    <td class="px-3 py-2 whitespace-nowrap">
                        {{formatar_moeda($icmsST)}}
                    </td>
                    <td class="px-3 py-2 whitespace-nowrap">
                        {{ $percentualICMSST }}
                    </td>
                    <td class="px-3 py-2 whitespace-nowrap">
                        {{formatar_moeda($ipi)}}
                    </td>
                    <td class="px-3 py-2 whitespace-nowrap">
                        {{ $percentualIPI }}    
                    </td>
                    <td class="px-3 py-2 whitespace-nowrap">
                        R$ 0,00
                    </td>
                    <td class="px-3 py-2 whitespace-nowrap">
                        0,00 %
                    </td>
                    <td class="px-3 py-2 whitespace-nowrap">
                        {{formatar_moeda($pis)}}
                    </td>
                    <td class="px-3 py-2 whitespace-nowrap">
                        {{ $percentualPIS }}
                    </td>
                    <td class="px-3 py-2 whitespace-nowrap">
                        {{formatar_moeda($cofins)}}
                    </td>
                    <td class="px-3 py-2 whitespace-nowrap">
                        {{ $percentualCOFINS }}
                    </td>
                    <td class="px-3 py-2 whitespace-nowrap">
                        R$ 0,00
                    </td>
                    <td class="px-3 py-2 whitespace-nowrap">
                        0,00 %
                    </td>
                    <td class="px-3 py-2 whitespace-nowrap">
                        {{ formatar_moeda($cprb)}}
                    </td>
                    <td class="px-3 py-2 whitespace-nowrap">
                        {{ $percentualCPRB }}
                    </td>
                    <td class="px-3 py-2 whitespace-nowrap">
                        {{ formatar_moeda($csll)}}
                    </td>
                    <td class="px-3 py-2 whitespace-nowrap">
                        {{ $percentualCSLL }}
                    </td>
                    <td class="px-3 py-2 whitespace-nowrap">
                        {{ formatar_moeda($irpj)}}
                    </td>
                    <td class="px-3 py-2 whitespace-nowrap">
                        {{ $percentualIRPJ }}
                    </td>
                    <td class="px-3 py-2 whitespace-nowrap">
                        {{ formatar_moeda($faturamentoLiquido )}}
                    </td>

                </tr>
                @endforeach
            </tbody>

        </table>

    </div>
</x-filament-panels::page>
