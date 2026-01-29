## Objetivo
- Criar uma coluna customizada reutilizável no Filament v5 para exibir etiquetas (tags) da `NotaFiscalEletronica` como badges (sigla) com tooltip interativo (nome completo + valor), totalmente responsiva.
- Garantir performance com eager loading para evitar N+1 ao listar NFes.
- Adicionar testes automatizados cobrindo cenários com/sem etiquetas e monitorando queries.

## Levantamento do que já existe
- A NFe (`App\Models\NotaFiscalEletronica`) usa `HasTags`, expondo relação `tagged()` (polimórfica) com `with('tag')`.
- Já existe uma coluna customizada similar `TagDocsColumn` + view `tag-docs-column.blade.php`, com tooltip interativo via `x-tooltip` e layout `flex flex-wrap`.

## Implementação da Coluna Customizada
### 1) Criar uma nova coluna reutilizável
- Criar classe `App\Filament\Tables\Columns\TagBadgesColumn` estendendo `Filament\Tables\Columns\Column`.
- Definir `protected string $view = 'filament.tables.columns.tag-badges-column';`.
- Expor configurações via métodos públicos (acessíveis no Blade):
  - `showTagCode(bool|Closure $value)` (default: `false`, para exibir sigla por acrônimo do nome).
  - `maxVisible(int|Closure $value)` (default: `2`).
  - `emptyText(string|Closure $value)` (default: `—`).

### 2) Criar view seguindo o design system do Filament
- Criar `resources/views/filament/tables/columns/tag-badges-column.blade.php`.
- Renderizar badges com `<x-filament::badge>` e layout responsivo: `flex flex-wrap gap-1`.
- Exibir texto do badge:
  - Se `showTagCode=true` e existir `$tag->code`, usar o `code`.
  - Caso contrário, gerar a sigla via helper `getLabelTag($tagged->tag_name)`.
- Tooltip interativo com `x-tooltip` (HTML permitido, `interactive: true`, tema light), exibindo `tag_name` + `formatar_moeda(value)`.
- Tratamento quando não houver etiquetas: renderizar `emptyText` (ex.: `—`) com tipografia discreta.
- Para muitas etiquetas: mostrar as primeiras `maxVisible` como badges e um indicador “mais” com tooltip listando todas.

## Integração no Resource da NFe Entrada
### 3) Adicionar eager loading para evitar N+1
- Implementar eager loading no `NfeEntradaResource` via override de `getEloquentQuery()`:
  - `parent::getEloquentQuery()->with(['tagged.tag'])`.
- Isso garante que a listagem carregue `tagged` + `tag` em lote, evitando consultas por linha.

### 4) Exibir a coluna na tabela
- Atualizar `NfeEntradasTable` para incluir a nova coluna:
  - `TagBadgesColumn::make('tagged')->label('Etiqueta')...`
- Manter o padrão de SoC já usado no projeto (lógica de tabela em `NfeEntradasTable`).

## Testes Automatizados
### 5) Testes de renderização e comportamento
- Criar `tests/Feature/Filament/TagBadgesColumnTest.php` (Laravel `Tests\TestCase` + `RefreshDatabase`).
- Cenários:
  - Sem etiquetas: garante que renderiza `emptyText`.
  - 1–2 etiquetas: garante que renderiza os badges (sigla) e inclui o `x-tooltip` com nome+valor.
  - >2 etiquetas: garante que limita badges e exibe o indicador “mais”.

### 6) Teste de N+1 via query log
- Criar múltiplas NFes com múltiplas etiquetas.
- Habilitar `DB::enableQueryLog()` e, após `flushQueryLog()`, executar `NfeEntradaResource::getEloquentQuery()->get()` e acessar `$nfe->tagged->first()->tag->category` em loop.
- Asserção: contagem de queries permanece baixa (ex.: <= 5), validando ausência de N+1.

## Arquivos que serão criados/alterados
- Criar: `app/Filament/Tables/Columns/TagBadgesColumn.php`
- Criar: `resources/views/filament/tables/columns/tag-badges-column.blade.php`
- Alterar: `app/Filament/Resources/NfeEntradas/NfeEntradaResource.php` (eager loading)
- Alterar: `app/Filament/Resources/NfeEntradas/Tables/NfeEntradasTable.php` (nova coluna)
- Criar: `tests/Feature/Filament/TagBadgesColumnTest.php`

## Validação
- Rodar suite de testes (`php artisan test`).
- Confirmar que a listagem de NFes exibe badges e tooltip corretamente e sem N+1.