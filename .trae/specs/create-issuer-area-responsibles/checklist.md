# Checklist

- [x] Enum `AreaAtendimentoEnum` contém todas as áreas obrigatórias: consultor, contas a pagar, fechamento, departamento pessoal, gerente, financeiro e cobrança.
- [x] Migration cria a tabela `issuer_area_responsibles` com as colunas `tenant_id`, `issuer_id`, `user_id` e `area`.
- [x] Model `IssuerAreaResponsible` possui relacionamentos corretos com `Issuer`, `User` e `Tenant`.
- [x] Resource Filament está no diretório correto: `app/Filament/Condominio/Resources/IssuerAreaResponsibles/`.
- [x] O Form Schema permite selecionar o usuário do sistema e a área de atendimento.
- [x] A tabela exibe os responsáveis por cada área de atendimento de forma clara.
- [x] A seleção de responsáveis para cada área é opcional, conforme os critérios de aceitação.
- [x] O mesmo usuário pode ser atribuído a múltiplas áreas para a mesma empresa.
