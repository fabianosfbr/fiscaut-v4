Diretrizes do Projeto: Laravel 12 + Filament v5
Este documento define as regras obrigatórias para a geração de código, refatoração e execução de tarefas neste workspace.

1. Ambiente de Execução (Laravel Sail)
Comando Obrigatório: Todos os comandos de CLI devem ser prefixados com sail.
Certo: sail artisan make:filament-resource
Errado: php artisan ... ou composer ...
Gerenciamento de Assets: Utilize o Vite através do Sail: sail npm run dev.

2. Padrões de Código (PHP 8.4 & Laravel 12)
Tipagem Estrita: Sempre declare declare(strict_types=1); no topo dos arquivos.
Modern PHP: Utilize Property Hooks e Constructor Property Promotion sempre que aplicável.
Injeção de Dependência: Prefira injeção via construtor em vez do helper app().

3. Padrões FilamentPHP v5
Componentes: Utilize exclusivamente a sintaxe da v5. Evite métodos depreciados das versões 3 ou 4.
Performance: Sempre implemente query() otimizado nos Resources para evitar o problema de N+1 queries.

Organização:
Resources em: app/Filament/Resources/
Widgets customizados em: app/Filament/Widgets/
UX: Todo formulário deve conter validação clara e tooltips em campos complexos.

4. Banco de Dados e Migrations
Migrations: Use a sintaxe de classe anônima do Laravel 12.


5. Testes Automatizados
Framework: Use Pest PHP.
Regra: Cada novo Resource do Filament deve vir acompanhado de um arquivo de teste em tests/Feature/Filament/....