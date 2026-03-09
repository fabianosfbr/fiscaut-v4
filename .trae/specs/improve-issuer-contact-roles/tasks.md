# Tasks - Melhoria na Gestão de Funções de Contatos

- [x] Task 1: Criar a migração para adicionar o campo `funcao` na tabela `issuer_contacts`.
  - [x] SubTask 1.1: Criar arquivo de migração: `2026_03_09_140000_add_funcao_to_issuer_contacts_table.php`.
  - [x] SubTask 1.2: Adicionar a coluna `funcao` (string) como obrigatória.
  - [x] SubTask 1.3: Rodar a migração usando o Sail.

- [x] Task 2: Criar o Enum `IssuerContactRoleEnum`.
  - [x] SubTask 2.1: Criar o arquivo `app/Enums/IssuerContactRoleEnum.php`.
  - [x] SubTask 2.2: Definir as opções para Condomínio e Associação no Enum.
  - [x] SubTask 2.3: Implementar o método `getOptions(IssuerTypeEnum $issuerType)` para retornar as opções corretas.

- [x] Task 3: Atualizar o Model `IssuerContact`.
  - [x] SubTask 3.1: Adicionar o cast para o novo Enum `IssuerContactRoleEnum`.

- [x] Task 4: Atualizar o Formulário `IssuerContactForm`.
  - [x] SubTask 4.1: Adicionar o campo `Select::make('funcao')` ao formulário.
  - [x] SubTask 4.2: Implementar a lógica reativa para carregar as opções baseadas no `issuer_type` da empresa associada.
  - [x] SubTask 4.3: Definir o campo como obrigatório.

- [x] Task 5: Atualizar a Tabela `IssuerContactsTable` (Opcional, mas recomendado).
  - [x] SubTask 5.1: Adicionar a coluna de função na listagem de contatos.

# Task Dependencies
- Task 2 deve ser completada antes de Task 3 e Task 4.
- Task 1 deve ser completada antes de rodar os testes.
