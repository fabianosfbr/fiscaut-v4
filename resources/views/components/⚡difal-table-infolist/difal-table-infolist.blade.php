<div>
    @if (empty($difals))
        <div class="p-4 bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 rounded-lg">
            <p>Não há produtos com DIFAL a calcular. Isso pode ocorrer quando a alíquota de destino é igual ou menor que
                a alíquota de origem, ou quando não há base de cálculo para os produtos.</p>
        </div>
    @else
        {{ $this->table }}
    @endif
</div>
