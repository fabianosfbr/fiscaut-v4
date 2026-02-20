# Fiscaut v4.1 — Documentação Técnica (Índice)

Bem-vindo(a) à documentação técnica do **Fiscaut**, uma plataforma proprietária de gestão fiscal e administrativa voltada a cenários complexos (regras tributárias, automações fiscais e multi-tenant), construída sobre a stack **TALL** (Tailwind, Alpine.js, Laravel, Livewire) com **FilamentPHP** como painel administrativo.

Esta página funciona como **README central da pasta `docs/`**, apontando para os guias mais importantes, descrevendo a arquitetura em alto nível e estabelecendo convenções de navegação para desenvolvedores.

---

## Visão Geral do Projeto

O Fiscaut busca reduzir o atrito entre a legislação fiscal brasileira e a operação diária de empresas e escritórios, oferecendo um hub para:

- **Emissores (Companies / Issuers)**: entidades que emitem documentos fiscais
- **Assinantes (Tenants / Subscribers)**: conta/cliente que gerencia um ou mais emissores
- **Regras e Tabelas Fiscais**: CFOP, CNAE, Simples Nacional, alíquotas e relacionamentos

### Stack e versões (referência do projeto)
- **Backend:** Laravel 12 / PHP 8.2+
- **Frontend:** Livewire 4 / Alpine.js / Tailwind CSS
- **Admin Panel:** FilamentPHP v5
- **Filas/Monitoramento:** Laravel Horizon
- **Banco de dados:** MySQL 8.0

---

## Guias Principais

Use a lista abaixo como ponto de partida. Quando for criar/alterar uma feature, procure primeiro o guia mais próximo do tema.

| Guia | Descrição |
| :--- | :--- |
| [Project Overview](./project-overview.md) | Objetivos, roadmap e contexto do produto. |
| [Architecture Notes](./architecture.md) | Arquitetura, módulos, dependências e ADRs. |
| [Filament Admin](./filament-admin.md) | Inventário de Resources, Pages, Widgets e Actions do painel. |
| [Development Workflow](./development-workflow.md) | Setup, branching, CI/CD e convenções. |
| [Testing Strategy](./testing-strategy.md) | Padrões de testes (Pest/PHPUnit) e organização. |
| [Glossary & Domain](./glossary.md) | Glossário do domínio fiscal e termos de negócio. |
| [Security & Compliance](./security.md) | Autenticação, segredos e LGPD. |
| [Application Services](./services.md) | Serviços centrais e integrações externas. |
| [Background Jobs](./jobs.md) | Filas, processamento assíncrono e pipelines (ex.: SEFAZ). |
| [Horizon Ops](../../docs/horizon-producao.md) | Operação e configuração do Horizon em produção. |
| [XmlReaderService](./xml-reader-service.md) | Convenções de parsing XML e padrões de acesso por array. |

> Observação: se um guia ainda não existir no repositório, crie-o seguindo as convenções de `docs/` e adicione aqui.

---

## Arquitetura (alto nível)

O projeto segue um modelo de **Monólito Modular** com Laravel + Filament. A interface administrativa é reativa usando **Livewire** (estado no servidor) e **Alpine.js** (interações locais).

### Componentização (Filament)

Há forte integração com componentes Filament e assets de UI (incluindo componentes compilados sob `public/js/filament/`). Isso afeta principalmente:

- **Form components** (ex.: rich editor, textarea, tags input, key-value etc.)
- **Table columns** (ex.: toggle, checkbox, text input etc.)
- **Widgets** e esquemas de layout (tabs, wizard, stats overview)

#### Convenção importante
- **Fontes “originais”** normalmente vivem em `vendor/filament/...` ou no código do app (ex.: `app/Filament/...`).
- **Assets compilados** (ex.: `public/js/filament/...`) são úteis para entender comportamento em runtime, mas **não devem ser tratados como fonte primária** para alterações (a menos que o projeto customize e compile deliberadamente esses assets).

---

## Multi-tenancy (conceito)

O Fiscaut implementa multi-tenancy com escopo por **Tenant** (assinante). Em geral:

- Um **Tenant** pode gerenciar múltiplos **Issuers**
- Dados e permissões devem respeitar o escopo do tenant
- Recursos administrativos (Filament Resources) costumam aplicar esse escopo automaticamente (via policies/guards/scopes definidos no app)

Detalhes operacionais e padrões de implementação devem ser documentados em:
- [`docs/architecture.md`](./architecture.md)
- [`docs/security.md`](./security.md)
- [`docs/filament-admin.md`](./filament-admin.md)

---

## Admin Resources (Filament) — visão geral

Os recursos administrativos ficam em `app/Filament/Resources/`. Para um inventário atualizado, consulte:

- **[Filament Admin](./filament-admin.md)**

Categorias comuns de recursos:

### 1) Gestão de Entidades
- **Issuers (`IssuerResource`)**: emissores, regimes e metadados fiscais
- **Tenants (`TenantResource`)**: assinantes, perfis, acesso e configuração
- **Category Tags (`CategoryTagResource`)**: sistema global de tags

### 2) Configuração Fiscal e Tributária
- **CFOP (`CfopResource`)**
- **CNAE (`CnaeResource`)**
- **Códigos de Serviço / ISS (`CodigoServicoResource`)**
- **Acumuladores (`AcumuladoresResource`)** (agregações por período/regras)

### 3) Módulo Simples Nacional
- **Anexos (`SimplesNacionalAnexoResource`)**
- **Alíquotas (`SimplesNacionalAliquotaResource`)**

---

## Estrutura do Repositório (referência rápida)

```text
├── app/
│   ├── Filament/       # Resources, Pages, Widgets do admin
│   ├── Models/         # Modelos Eloquent do domínio fiscal
│   └── Actions/        # Casos de uso / lógica reutilizável
├── config/             # Configurações da aplicação
├── database/
│   ├── migrations/     # Evolução do schema
│   └── seeders/        # Seeds (ex.: CFOP, CNAE)
├── docs/               # Índice e guias técnicos
├── public/js/filament/ # Assets compilados do admin (runtime)
├── resources/
│   ├── views/          # Blade / Livewire views
│   └── lang/           # Localização (pt_BR)
└── tests/              # Testes automatizados (Pest)
```

---

## Setup de Desenvolvimento (resumo)

Pré-requisitos: **Docker** e/ou ambiente com **PHP 8.2+**, Node/NPM, Composer.

1) Clonar e instalar dependências:
```bash
git clone [repository-url]
cd fiscaut-v4.1
composer install
npm install
```

2) Configurar ambiente:
```bash
cp .env.example .env
php artisan key:generate
```

3) Build de assets:
```bash
npm run dev
```

4) Banco de dados:
```bash
php artisan migrate --seed
```

5) Criar usuário admin do Filament:
```bash
php artisan make:filament-user
```

> Para detalhes (Docker, seeds, serviços externos, ambientes), centralize em `docs/development-workflow.md`.

---

## Confidencialidade e Segurança

> **AVISO (Proprietário e Confidencial):** O Fiscaut é um aplicativo comercial proprietário. Código-fonte, schemas, dados e documentação são confidenciais. É proibido compartilhar credenciais, dados de clientes ou detalhes sensíveis sem autorização.

Para vulnerabilidades e conformidade (LGPD), consulte:
- **[Security & Compliance](./security.md)**

---

## Como contribuir com a documentação

- Mantenha os guias em `docs/` **curtos e acionáveis**
- Prefira seções do tipo:
  - **O que é**
  - **Quando usar**
  - **Como funciona**
  - **Exemplos**
  - **Armadilhas / troubleshooting**
- Ao criar um novo documento, **adicione-o na tabela “Guias Principais”** acima
- Referencie arquivos e pastas reais do repositório (ex.: `app/Filament/...`, `tests/...`) para facilitar navegação

---
