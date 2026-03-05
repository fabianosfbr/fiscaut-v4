---
title: Checklist - Chat IA flutuante (FAB)
status: draft
---

## Funcional

- FAB fixo visível e clicável em páginas do painel
- Abrir/fechar funciona e mantém histórico enquanto a página está aberta
- Envio por Enter funciona; envio concorrente é bloqueado
- Loading/“digitando” aparece durante simulação de resposta
- Erro (mock) aparece no UI e permite tentar novamente

## UI/UX

- Painel responsivo (mobile e desktop)
- Scroll da conversa preserva a posição ao receber novas mensagens
- Foco no input ao abrir
- Botões com estados disabled corretos

## Segurança

- Resposta do assistente renderizada como texto (sem HTML)
- Nenhuma chave/segredo exposto no client
- Logs sem conteúdo sensível por padrão

## Testes

- Testes Livewire cobrindo sucesso e erro
- Testes não dependem de migrations frágeis (evitar RefreshDatabase se necessário)
