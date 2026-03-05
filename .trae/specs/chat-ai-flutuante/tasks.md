---
title: Tarefas - Chat IA flutuante (FAB)
status: draft
---

## Implementação

1. Criar template do componente
   - Adicionar `resources/views/components/⚡chat-ai/chat-ai.blade.php` com FAB + painel.
   - Aplicar classes Tailwind/Filament para manter consistência visual.

2. Implementar lógica Livewire v4
   - Atualizar `resources/views/components/⚡chat-ai/chat-ai.php` com estados, actions e validações.
   - Garantir fluxo: toggle/open/close/send e estados de loading/erro.

3. Mockar respostas do assistente
   - Implementar estratégia de resposta mock (lista fixa e/ou echo da pergunta).
   - Simular latência para demonstrar loading.
   - Prever um caminho de erro mock para validar UX (ex.: comando “/erro”).

4. Ponto de inclusão global
   - Incluir o componente em um layout compartilhado do painel (ex.: Filament panel layout/hook).
   - Adicionar configuração/feature flag para habilitar/desabilitar.

## Qualidade

5. Testes
   - Teste Livewire: envio de mensagem adiciona resposta e controla loading.
   - Teste Livewire: erro mock apresenta mensagem amigável e não duplica mensagens.

6. Revisão de segurança
   - Garantir renderização de resposta como texto (sem HTML).
   - Garantir ausência de logs com payload sensível.
