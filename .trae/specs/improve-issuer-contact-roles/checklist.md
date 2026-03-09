# Checklist - Melhoria na Gestão de Funções de Contatos

- [x] A migração para adicionar o campo `funcao` foi criada e executada com sucesso.
- [x] O Enum `IssuerContactRoleEnum` foi criado com todas as opções de Condomínio e Associação.
- [x] O model `IssuerContact` possui o cast correto para `funcao`.
- [x] O formulário `IssuerContactForm` mostra o campo `funcao` como obrigatório.
- [x] O campo `funcao` mostra as opções corretas para uma empresa do tipo "Condomínio".
- [x] O campo `funcao` mostra as opções corretas para uma empresa do tipo "Associação".
- [x] A função do contato é salva corretamente no banco de dados.
- [x] A função do contato é exibida corretamente na listagem (`IssuerContactsTable`).
