# Changelog

Este documento registra alterações relevantes do projeto (features, correções e mudanças de comportamento), organizado por versão e data.

## Como ler este changelog

Cada release (ou conjunto de mudanças) é dividido em:

- **Added**: novas funcionalidades, módulos, recursos e integrações.
- **Changed**: mudanças de comportamento, refactors e alterações que podem impactar uso existente.
- **Fixed**: correções de bugs e ajustes de qualidade.

> Convenção: a seção **Unreleased** contém mudanças já implementadas no repositório, mas ainda não publicadas como release.

---

## [Unreleased] - 2026-01-30

### Added

#### XML Processing Engine

Nova camada de leitura/processamento de XML com serviços especializados por tipo de documento fiscal.

- **`XmlNfeReaderService`**
  - Serviço dedicado para parsing de **NFe**.
  - Objetivo: substituir leitura “genérica” por uma abordagem com estrutura de retorno previsível (arrays associativos), facilitando validação e consumo por camadas superiores.

- **`XmlCteReaderService`**
  - Serviço dedicado para parsing de **CTe**.
  - Mesmo objetivo: especialização, padronização e previsibilidade do formato de saída.

- **Substituição de serviços legados**
  - `NfeService` e `CteService` (legados) foram substituídos por readers baseados em **arrays**.
  - Motivação principal: reduzir ambiguidade de tipos/estruturas e corrigir inconsistências ao ler XMLs variados.

**Impacto para developers**
- Se algum módulo chamava diretamente `NfeService`/`CteService`, deve migrar para os novos *readers* correspondentes.
- A expectativa de retorno agora é **array associativo tipado/estruturado**, evitando objetos mistos ou estruturas inconsistentes.

---

#### Filament Resources

Novos recursos/gerenciadores no Filament para administrar categorias de etiquetas e suas tags relacionadas.

- **`CategoryTagResource`**
  - Novo resource para gestão de **“Categorias das etiquetas”**.
  - Fornece CRUD no painel administrativo.

- **`TagsRelationManager`**
  - Relation manager integrado ao resource de categoria.
  - Permite gerenciar **tags relacionadas** diretamente dentro do contexto da categoria.

**Onde isso se encaixa**
- A base de UI do projeto utiliza componentes do Filament (schemas, forms, tables, widgets).  
  Consulte a estrutura em:
  - `public/js/filament/schemas`
  - `public/js/filament/forms/components`
  - `public/js/filament/tables/components/columns`
  - `public/js/filament/widgets/components`

---

#### UI Components

- **Livewire**
  - Adicionado um componente/ponte (“sou um componente Livewire”) nas páginas de **Configuration**.
  - Objetivo: integração customizada entre páginas de configuração e um componente Livewire específico.

**Impacto para developers**
- Páginas de configuração podem depender desse bridge; ao customizar/estender a UI, valide se o componente Livewire está presente e corretamente registrado.

---

### Changed

#### CnaeForm

Melhorias no campo `aliquota` para suportar entrada decimal conforme padrão do Filament v5.

- **Máscara decimal (Filament v5)**
  - Campo `aliquota` agora aceita formatos como:
    - `10,50`
    - `0,50`
- **Persistência com “raw value stripping”**
  - Antes de salvar, o valor formatado é convertido para um formato “cru” (sem máscara), evitando persistir caracteres de formatação.

**Impacto para developers**
- Se existiam validações/customizações no `aliquota`, revise para garantir compatibilidade com a nova máscara e com o valor “limpo” no backend.
- Testes/seeders que setavam `aliquota` podem precisar ajustar o formato aceito.

---

### Fixed

#### XML Parsing

- Corrigidos problemas na leitura genérica de XML ao **tipar estritamente** a estrutura de retorno como **array associativo**.
- Resultado: menor chance de erros por formatos inesperados e maior previsibilidade para consumo (ex.: mapeamentos, validações e persistência).

**Sinais típicos corrigidos**
- Campos ausentes gerando estruturas inconsistentes.
- Retornos variando entre string/objeto/array dependendo do XML de origem.

---

## Guia rápido para contribuir com entradas no changelog

Ao adicionar uma mudança:

1. Inclua na seção **[Unreleased]**.
2. Use um dos blocos: **Added / Changed / Fixed**.
3. Prefira descrições:
   - orientadas a impacto (o que muda para quem usa),
   - com nomes exatos de classes/recursos,
   - indicando substituições (o que foi descontinuado e o que usar no lugar).

Exemplo:

```md
### Changed
- **FooService**: Substituído por **BarService** para padronizar retornos como array associativo.
```

---
