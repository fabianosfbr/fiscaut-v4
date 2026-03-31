# Testes de Auditoria Automática de Manutenções

Este documento descreve os testes para validar o funcionamento do sistema de auditoria automática implementado com o ManutencaoObserver.

## Visão Geral

O ManutencaoObserver monitora alterações nos seguintes campos da tabela `manutencoes`:
- `custo_real`
- `usuario_responsavel`
- `data_programada`
- `data_execucao`
- `data_conclusao`
- `prioridade`
- `status`

## Testes Recomendados

### 1. Teste de Alteração de Status

**Objetivo:** Verificar se alterações no status geram histórico adequado.

**Procedimento:**
1. Crie uma manutenção com status "Programada"
2. Altere o status para "Em Andamento"
3. Verifique a tabela `manutencao_historicos`

**Resultado Esperado:**
- Registro no histórico com:
  - `campo_alterado`: "status"
  - `observacoes`: "Alteração de status de 'Programada' para 'Em Andamento'"

### 2. Teste de Alteração de Custo

**Objetivo:** Verificar se alterações no custo real geram histórico adequado.

**Procedimento:**
1. Crie uma manutenção com custo real de R$ 100,00
2. Altere o custo real para R$ 150,00
3. Verifique a tabela `manutencao_historicos`

**Resultado Esperado:**
- Registro no histórico com:
  - `campo_alterado`: "custo_real"
  - `observacoes`: "Alteração do custo real de R$ 100,00 para R$ 150,00"

### 3. Teste de Alteração de Data

**Objetivo:** Verificar se alterações nas datas geram histórico adequado.

**Procedimento:**
1. Crie uma manutenção com data programada para "2024-01-15"
2. Altere a data programada para "2024-01-20"
3. Verifique a tabela `manutencao_historicos`

**Resultado Esperado:**
- Registro no histórico com:
  - `campo_alterado`: "data_programada"
  - `observacoes`: "Alteração da data de programação de 15/01/2024 para 20/01/2024"

### 4. Teste de Alteração de Responsável

**Objetivo:** Verificar se alterações no responsável geram histórico adequado.

**Procedimento:**
1. Crie uma manutenção sem responsável definido
2. Altere o responsável para "João Silva"
3. Verifique a tabela `manutencao_historicos`

**Resultado Esperado:**
- Registro no histórico com:
  - `campo_alterado`: "usuario_responsavel"
  - `observacoes`: "Alteração do responsável de 'não definido' para 'João Silva'"

### 5. Teste de Alteração de Prioridade

**Objetivo:** Verificar se alterações na prioridade geram histórico adequado.

**Procedimento:**
1. Crie uma manutenção com prioridade "Média"
2. Altere a prioridade para "Alta"
3. Verifique a tabela `manutencao_historicos`

**Resultado Esperado:**
- Registro no histórico com:
  - `campo_alterado`: "prioridade"
  - `observacoes`: "Alteração da prioridade de 'Média' para 'Alta'"

### 6. Teste de Múltiplas Alterações

**Objetivo:** Verificar se múltiplas alterações em um único update geram múltiplos registros de histórico.

**Procedimento:**
1. Crie uma manutenção com status "Programada" e custo R$ 100,00
2. Atualize simultaneamente status para "Em Andamento" e custo para R$ 120,00
3. Verifique a tabela `manutencao_historicos`

**Resultado Esperado:**
- Dois registros no histórico:
  1. Alteração de status
  2. Alteração de custo

### 7. Teste de Usuário Anônimo

**Objetivo:** Verificar o comportamento quando não há usuário autenticado.

**Procedimento:**
1. Crie uma manutenção via console/seed sem autenticação
2. Atualize algum campo
3. Verifique a tabela `manutencao_historicos`

**Resultado Esperado:**
- Registro no histórico com `usuario_id` nulo

## Comandos de Teste

### Criar Manutenção de Teste
```php
$manutencao = Manutencao::create([
    'issuer_id' => 1,
    'tipo_manutencao_id' => 1,
    'tipo' => 'preventiva',
    'status' => 'programada',
    'prioridade' => 'media',
    'data_programada' => '2024-01-15',
    'custo_estimado' => 100.00,
    'descricao' => 'Teste de auditoria'
]);
```

### Atualizar Manutenção
```php
$manutencao->update([
    'status' => 'em_andamento',
    'custo_real' => 120.00,
    'data_execucao' => now()
]);
```

### Consultar Histórico
```php
$historicos = ManutencaoHistorico::where('manutencao_id', $manutencao->id)
    ->orderBy('created_at', 'desc')
    ->get();
```

## Validação de Integridade

### Verificar Observador Ativo
```php
// Verificar se o observer está registrado
$observers = Manutencao::getObservables();
dd($observers);
```

### Verificar Atributo de Observer
```php
// Verificar se o atributo está presente
$reflection = new ReflectionClass(Manutencao::class);
$attributes = $reflection->getAttributes(ObservedBy::class);
dd($attributes);
```

## Considerações Importantes

1. **Performance:** O observer é executado em cada update, mas apenas cria histórico para campos alterados
2. **Segurança:** O histórico armazena valores anteriores e novos, permitindo auditoria completa
3. **Integridade:** O campo `usuario_id` pode ser nulo quando não há usuário autenticado
4. **Formato:** As observações são geradas automaticamente com mensagens descritivas

## Possíveis Problemas

1. **Observer não registrado:** Verifique se o atributo `#[ObservedBy([ManutencaoObserver::class])]` está presente
2. **Campos não auditáveis:** Apenas os campos especificados no observer geram histórico
3. **Usuário não autenticado:** Em operações via console, o `usuario_id` será nulo
4. **Formato de data:** O observer usa Carbon para formatar datas no padrão brasileiro

## Integração com Filament

O observer funciona independentemente do Filament, mas se integra perfeitamente:
- Alterações feitas via Filament Resources geram histórico
- O histórico pode ser visualizado no ManutencaoHistoricoResource
- Não interfere no fluxo normal de edição do Filament