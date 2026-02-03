## Objetivo
- Criar um novo relatório “Listagem de Produtos” no grupo “Relatórios”, seguindo o padrão de [ListagemCliente.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Pages/Relatorio/ListagemCliente.php) e [ListagemFornecedor.php](file:///root/projetos/fiscaut-v4.1/app/Filament/Pages/Relatorio/ListagemFornecedor.php), mas extraindo itens do XML via atributo produtos de [NotaFiscalEletronica.php](file:///root/projetos/fiscaut-v4.1/app/Models/NotaFiscalEletronica.php#L108-L156).

## Arquivos
- Adicionar: `app/Filament/Pages/Relatorio/ListagemProduto.php`
- Adicionar: `resources/views/filament/pages/relatorio/listagem-produto.blade.php`

## Implementação (Filament Table com Custom Data)
- Page:
  - `protected static ?string $navigationLabel = 'Listagem de Produtos'`
  - `protected static ?string $title = 'Listagem de Produtos'`
  - `protected static ?string $slug = 'listagem-produtos'`
  - `protected static string|UnitEnum|null $navigationGroup = 'Relatórios'`
  - `protected string $view = 'filament.pages.relatorio.listagem-produto'`
- Tabela:
  - Usar `->records(...)` (custom data) retornando `LengthAwarePaginator`.
  - Dentro de `records()`:
    - Buscar `Auth::user()->currentIssuer`.
    - Consultar NFEs relevantes (por padrão, “saídas”: `status_nota = 100` e `emitente_cnpj = issuer->cnpj`) buscando apenas colunas necessárias (`id`, `xml`).
    - Iterar NFEs em lote (`chunkById`) e, para cada NFE, iterar `$nfe->produtos`.
    - Agrupar produtos por uma chave estável (ex.: hash de `cProd|xProd|NCM|CFOP|uCom`) e somar:
      - `total_qCom` (soma de `qCom`)
      - `total_vProd` (soma de `vProd`)
      - opcional: contador de ocorrências/itens.
    - Aplicar busca manual quando `$search` vier preenchido (em `cProd`, `xProd`, `NCM`, `CFOP`, `cEAN`).
    - Aplicar ordenação manual quando `$sortColumn`/`$sortDirection` vierem preenchidos; caso contrário, ordenar por `total_vProd desc`.
    - Paginar manualmente com `$page` e `$recordsPerPage` e retornar `new LengthAwarePaginator(...)`.
  - Definir colunas (todas baseadas em chaves do array do record):
    - `xProd` (Produto) com `searchable()` e `sortable()`
    - `cProd` (Código) com `searchable()` e `sortable()`
    - `NCM`, `CFOP`, `uCom`
    - `total_qCom` (Quantidade Total)
    - `total_vProd` (Valor Total) com `money('BRL')`
  - Desabilitar URL de registro (`->recordUrl(null)`) para evitar clique em linha tentar navegar.

## Padrões / Cuidados
- Converter valores do XML com segurança (ex.: `qCom`, `vProd` podem vir como string) para `float`/`decimal` antes de somar.
- Garantir que cada record tenha uma chave/ID única e consistente (importante para o diffing do Livewire).
- Manter o Blade igual aos outros relatórios: apenas renderizar `{{ $this->table }}`.

## Validação
- Validar manualmente no painel:
  - Carregamento do relatório sem exceções.
  - Ordenação (por valor total) e busca funcionando.
  - Paginação funcionando.
- Adicionar um teste unitário simples (opcional, mas recomendado) para a função de agregação, usando um XML fixture mínimo para garantir que o agrupamento e as somas batem.
