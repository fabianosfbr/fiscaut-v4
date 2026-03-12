# Tasks - Gestão de Documentos AGEs da Empresa (IssuerAge)

- [x] Task 1: Atualizar o Model `IssuerAge`:
  - [x] SubTask 1.1: Adicionar relacionamento com `Issuer`.
  - [x] SubTask 1.2: Adicionar campos no `$guarded` ou `$fillable`.
  - [x] SubTask 1.3: Adicionar `$casts` para as datas.
- [x] Task 2: Atualizar a Migration de `issuer_ages`:
  - [x] SubTask 2.1: Adicionar colunas: `issuer_id`, `document_path`, `vigencia_date`, `data_limite_edital`, `prazo_tecnico`, `observacoes`.
  - [x] SubTask 2.2: Executar a migration.
- [x] Task 3: Implementar `IssuerAgeForm`:
  - [x] SubTask 3.1: Criar a aba `Aba_Essenciais`.
  - [x] SubTask 3.2: Adicionar campo `Select` para `issuer_id`.
  - [x] SubTask 3.3: Adicionar campo `FileUpload` para o documento da AGE com a lógica de diretório.
  - [x] SubTask 3.4: Adicionar campos de data (`vigencia_date`, `data_limite_edital`).
  - [x] SubTask 3.5: Adicionar campos de texto (`prazo_tecnico`, `observacoes`).
- [x] Task 4: Implementar `IssuerAgesTable`:
  - [x] SubTask 4.1: Adicionar colunas para exibição na listagem.
  - [x] SubTask 4.2: Garantir que a exclusão solicite confirmação.
- [x] Task 5: Validação:
  - [x] SubTask 5.1: Verificar se os documentos estão sendo salvos no caminho correto.
  - [x] SubTask 5.2: Verificar se os prazos e vigências estão sendo capturados corretamente.

# Task Dependencies
- Task 2 depende de Task 1.
- Task 3 depende de Task 2.
- Task 4 depende de Task 3.
- Task 5 depende de todas as tarefas anteriores.
