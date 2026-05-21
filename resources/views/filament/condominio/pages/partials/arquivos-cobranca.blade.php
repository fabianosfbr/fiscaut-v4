@if(!empty($arquivos))
    <div class="space-y-4">
        <div class="grid grid-cols-1 gap-2">
            @foreach($arquivos as $arquivo)
                <div class="flex items-center justify-between p-3 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="shrink-0">
                            
                            <x-filament::icon-button
                                icon="heroicon-m-paper-clip"
                            />
                        </div>
                        <div class="min-w-0">
                            <div class="font-medium text-sm text-gray-900 truncate">
                                {{ data_get($arquivo, 'st_nome_arq') }}
                            </div>
                            <div class="text-xs text-gray-500 truncate">
                                {{ data_get($arquivo, 'st_extensao_arq') }} — {{ number_format((int) data_get($arquivo, 'nm_tamanho_arq') / 1024, 2, ',', '.') }} KB
                            </div>
                        </div>
                    </div>
                    <div class="flex gap-2 shrink-0">
                        <a
                            href="{{ route('condominio.conta-pagar.download-arquivo', ['id' => data_get($arquivo, 'id_arquivo_arq'), 'hash' => data_get($arquivo, 'st_hash_arq')]) }}"
                            class="inline-flex items-center gap-2 px-3 py-1.5 text-xs font-medium text-white bg-primary-600 hover:bg-primary-500 rounded-md transition"
                            target="_blank"
                        >                            
                            Visualizar
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@else
    <div class="text-center py-8">
        <x-heroicon-o-document class="w-12 h-12 text-gray-300 mx-auto mb-3" />
        <p class="text-gray-500">Nenhum arquivo anexado a esta cobrança.</p>
    </div>
@endif
