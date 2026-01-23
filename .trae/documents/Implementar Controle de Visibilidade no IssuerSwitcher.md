# Plano de Implementação Revisado: Controle de Visibilidade via Configuração

## Objetivo
Implementar a restrição de visibilidade do `IssuerSwitcher` utilizando um arquivo de configuração dedicado para facilitar a gestão das rotas bloqueadas.

## 1. Criação do Arquivo de Configuração
Criaremos o arquivo `config/issuer-switcher.php` para centralizar as regras do componente.

**Conteúdo proposto para `config/issuer-switcher.php`:**
```php
<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Rotas Excluídas
    |--------------------------------------------------------------------------
    |
    | Aqui você pode listar os nomes das rotas onde o componente IssuerSwitcher
    | não deve ser renderizado. Isso é útil para evitar a exibição do seletor
    | em páginas de gerenciamento de empresas ou tenants.
    |
    */
    'exclude_routes' => [
        'filament.admin.resources.issuers.index',
        'filament.admin.resources.tenants.index',
        'filament.admin.resources.tenants.create',
        'filament.admin.resources.tenants.edit',
    ],
];
```

## 2. Atualização do Componente
Modificaremos o arquivo `resources/views/components/⚡issuer-switcher/issuer-switcher.php` para ler esta configuração.

**Alterações na classe anônima:**

```php
    public function render()
    {
        // Obtém a rota atual
        $currentRoute = request()->route()?->getName();

        // Obtém a lista de rotas excluídas do arquivo de configuração
        // Usa um array vazio como fallback caso a config não exista
        $hiddenRoutes = config('issuer-switcher.exclude_routes', []);

        // Verifica se a rota atual está na lista de bloqueio
        if (in_array($currentRoute, $hiddenRoutes)) {
            // Retorna um HTML vazio para "esconder" o componente
            return <<<'BLADE'
                <div></div>
            BLADE;
        }

        // Renderiza a view normal se não estiver bloqueado
        return view('components.⚡issuer-switcher.issuer-switcher');
    }
```

## Benefícios
- **Centralização:** Adicionar ou remover páginas bloqueadas torna-se simples editando apenas o arquivo de configuração, sem tocar no código do componente.
- **Manutenibilidade:** Separa a lógica (componente) da configuração (regras de negócio).
