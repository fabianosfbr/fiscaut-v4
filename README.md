# Fiscaut v4 - Plataforma de Gestão Fiscal e Administrativa

Bem-vindo ao **Fiscaut**, uma plataforma proprietária de gestão fiscal e administrativa. Este sistema foi desenvolvido para lidar com regulamentações fiscais complexas, configurações multi-tenant e automação fiscal utilizando a stack TALL (Tailwind, Alpine.js, Laravel, Livewire).

## 🚀 Visão Geral do Projeto

O Fiscaut foi projetado para preencher a lacuna entre a complexa legislação fiscal brasileira e a gestão empresarial automatizada. Ele serve como um hub centralizado para gerenciar emissores (Empresas) e a intrincada rede de regras fiscais (CFOP, CNAE, Simples Nacional).

- **Backend:** Laravel 12 / PHP 8.2+
- **Frontend:** Livewire 4 / Alpine.js / Tailwind CSS
- **Painel Administrativo:** FilamentPHP v5
- **Banco de Dados:** MySQL 8.0

---

## 🏗️ Arquitetura e Stack Tecnológica

### Framework & UI
A aplicação segue uma abordagem de **Monólito Modular** baseada em Laravel e Filament. A interface é altamente reativa, utilizando Livewire para gerenciamento de estado no lado do servidor e Alpine.js para interações no cliente.

### Sistema de Componentes
O repositório inclui uma integração profunda com componentes do Filament. Comportamentos personalizados são frequentemente estendidos via utilitários JavaScript:
- **Editores Ricos:** Manipulação personalizada para uploads de arquivos e mensagens de validação.
- **Selects:** Lógica avançada de consulta e filtragem.
- **Notificações:** Sistema robusto de notificações para feedback do usuário em tempo real.

### Multi-Tenancy
O Fiscaut implementa uma arquitetura multi-tenant onde os dados são escopados por **Tenant** (Assinante). Cada tenant pode gerenciar múltiplos **Emissores** (Empresas).

---

## 📂 Estrutura do Repositório

```text
├── app/
│   ├── Filament/       # Recursos, widgets e páginas do painel administrativo
│   ├── Models/         # Modelos Eloquent representando o domínio fiscal
│   └── Actions/        # Classes de lógica de negócio reutilizáveis
├── config/             # Configurações da aplicação e de terceiros
├── database/
│   ├── migrations/     # Evolução do esquema do banco de dados
│   └── seeders/        # Dados iniciais para códigos fiscais (CFOP, CNAE)
├── docs/               # Índice de documentação técnica
├── public/js/filament/ # Ativos compilados para a interface administrativa
├── resources/
│   ├── views/          # Templates Blade e componentes Livewire
│   └── lang/           # Localização (pt_BR)
└── tests/              # Suítes de testes automatizados (Pest)
```

---

## 🛠️ Configuração de Desenvolvimento

Para começar o desenvolvimento, certifique-se de ter o **Docker** e **PHP 8.2+** instalados.

1.  **Clonar o repositório:**
    ```bash
    git clone [repository-url]
    cd fiscaut-v4
    ```

2.  **Configuração do Ambiente:**
    ```bash
    cp .env.example .env
    composer install
    npm install && npm run dev
    ```

3.  **Migração do Banco de Dados:**
    ```bash
    php artisan migrate --seed
    ```

4.  **Acesso Administrativo:**
    Crie um usuário super-admin para acessar o painel do Filament:
    ```bash
    php artisan make:filament-user
    ```

---

## 🧪 Áreas de Foco para QA

Ao realizar a garantia de qualidade, foque nestes caminhos críticos:

1.  **Validação e Persistência de Formulários:** Verifique se os esquemas de formulários do Filament aplicam as restrições corretamente.
2.  **Reatividade de Componentes (Livewire):** Garanta que a interface permaneça sincronizada com o estado do servidor.
3.  **Interações de Tabela:** Teste ações em massa e atualizações imediatas em colunas de toggle e input de texto.
4.  **Permissões e Segurança:** Valide se os middlewares e políticas do Filament restringem o acesso adequadamente.

---

## 🛡️ Segurança e Confidencialidade

> [!WARNING]
> **Aviso de Confidencialidade:** O Fiscaut é uma aplicação comercial proprietária. Todo o código-fonte, esquemas de banco de dados e documentação são estritamente confidenciais. O compartilhamento não autorizado de credenciais, dados de clientes ou detalhes arquiteturais é proibido.

Para vulnerabilidades de segurança ou questões de conformidade (LGPD), consulte o guia de Segurança e Conformidade em `docs/security.md`.

---
*Última Atualização: 2026-03-05*
