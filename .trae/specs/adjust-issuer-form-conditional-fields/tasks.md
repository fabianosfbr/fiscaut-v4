# Tarefas

- [x] Tarefa 1: Ajustar visibilidade e obrigatoriedade do campo 'atividade' em `IssuerForm.php`
  - [x] Subtarefa 1.1: Adicionar lógica condicional ao método `required()` baseada no valor de `issuer_type`.
  - [x] Subtarefa 1.2: Adicionar lógica condicional ao método `visible()` baseada no valor de `issuer_type`.
- [x] Tarefa 2: Ajustar visibilidade e obrigatoriedade do campo 'contribuinte_icms' em `IssuerForm.php`
  - [x] Subtarefa 2.1: Adicionar lógica condicional ao método `required()` baseada no valor de `issuer_type`.
  - [x] Subtarefa 2.2: Adicionar lógica condicional ao método `visible()` baseada no valor de `issuer_type`.

# Dependências de Tarefas
- Ambas as tarefas dependem da análise prévia de `App\Enums\IssuerTypeEnum` e do funcionamento do componente `Select` com `live()` no campo `issuer_type`.
