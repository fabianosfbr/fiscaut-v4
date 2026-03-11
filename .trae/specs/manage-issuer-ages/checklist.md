# Checklist - Gestão de Documentos AGEs da Empresa (IssuerAge)

- [x] Model `IssuerAge` implementado com relacionamento `Issuer`.
- [x] Migration de `issuer_ages` atualizada com os campos corretos.
- [x] Formulário `IssuerAgeForm` implementado com a aba `Aba_Essenciais`.
- [x] Campo `FileUpload` configurado para salvar no caminho `rag/{tenant_id}/{cnpj}/documents`.
- [x] Campos de data (`vigencia`, `data_limite_edital`) e texto (`prazo_tecnico`, `observacoes`) funcionando.
- [x] Listagem `IssuerAgesTable` exibindo as informações das AGEs.
- [x] Exclusão de AGE solicita confirmação antes de remover.
- [x] Relacionamento com `Issuer` funcionando corretamente.
