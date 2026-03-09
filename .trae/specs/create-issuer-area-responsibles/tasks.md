# Tasks

- [x] Task 1: Criar o Enum `AreaAtendimentoEnum`: Definir as áreas consultor, contas a pagar, fechamento, departamento pessoal, gerente, financeiro e cobrança.
  - [x] SubTask 1.1: Criar o arquivo `app/Enums/AreaAtendimentoEnum.php`
  - [x] SubTask 1.2: Implementar o contrato `HasLabel` para exibição no Filament

- [x] Task 2: Criar o Model e Migration `IssuerAreaResponsible`: Criar a estrutura de banco de dados para armazenar as atribuições.
  - [x] SubTask 2.1: Criar a migration com `tenant_id`, `issuer_id`, `user_id` e `area`
  - [x] SubTask 2.2: Criar o model `app/Models/IssuerAreaResponsible.php` com os relacionamentos `issuer`, `user` e `tenant`
  - [x] SubTask 2.3: Configurar o cast do campo `area` para o Enum criado na Task 1

- [x] Task 3: Criar o Resource Filament `IssuerAreaResponsibleResource`: Implementar a interface administrativa para gerenciar os responsáveis de área.
  - [x] SubTask 3.1: Criar o Resource no diretório `app/Filament/Condominio/Resources/`
  - [x] SubTask 3.2: Criar o Form Schema em `Schemas/IssuerAreaResponsibleForm.php`
  - [x] SubTask 3.3: Criar a Table em `Tables/IssuerAreaResponsiblesTable.php`
  - [x] SubTask 3.4: Criar as páginas de Listagem, Criação e Edição

- [x] Task 4: Atualizar os Models `Issuer` e `User`: Adicionar os relacionamentos necessários para acessar os responsáveis de área.
  - [x] SubTask 4.1: Adicionar o relacionamento `areaResponsibles` em `app/Models/Issuer.php`
  - [x] SubTask 4.2: Adicionar o relacionamento `areaResponsibles` em `app/Models/User.php`

# Task Dependencies
- [Task 2] depende de [Task 1]
- [Task 3] depende de [Task 2]
- [Task 4] depende de [Task 2]
