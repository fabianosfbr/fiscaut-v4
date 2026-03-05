---
title: Chat IA flutuante (FAB)
status: draft
owner: equipe
---

## Contexto

Precisa existir um botão flutuante (FAB) que abre um formulário de chat para interação com um agente de IA. Nesta primeira entrega, as respostas do “agente” serão **mockadas** (sem integração real com IA). O componente deve ser implementado como Livewire v4 no arquivo:

- `resources/views/components/⚡chat-ai/chat-ai.php`

O markup deve ficar no template Blade correspondente:

- `resources/views/components/⚡chat-ai/chat-ai.blade.php`

## Objetivos

- Disponibilizar um FAB fixo no canto inferior direito que abre/fecha o chat.
- Permitir enviar perguntas e receber respostas **mockadas** do agente dentro do próprio painel.
- Manter o estado do chat reativo via Livewire (abrir/fechar, input, lista de mensagens, loading).
- Ser fácil de incluir em layouts/páginas como um único “widget” de UI.

## Não-objetivos

- Persistência definitiva de histórico no banco (pode ser sessão/memória inicialmente).
- Integração com IA nesta entrega (somente mock).
- Integração com múltiplos provedores/roteadores de IA no mesmo fluxo (quando houver integração real).
- Suporte a anexos/arquivos e rich text.

## Escopo de UI/UX

### Posição e comportamento

- FAB fixo no viewport (default: canto inferior direito).
- Ao clicar no FAB:
  - Abre um “painel” de chat (popover/drawer) acima do FAB, com altura e largura responsivas.
  - Mantém o foco no campo de texto.
- Ao fechar:
  - Não perde o histórico atual da conversa (enquanto a página estiver aberta).

### Layout do painel

- Cabeçalho: título “Assistente IA” + botão “Fechar”.
- Corpo: lista de mensagens com scroll.
  - Mensagens do usuário alinhadas à direita.
  - Mensagens do assistente alinhadas à esquerda.
- Rodapé: input + botão “Enviar”.
  - Envio por Enter; Shift+Enter quebra linha (se optar por textarea).
  - Estado de carregamento bloqueia múltiplos envios concorrentes.

### Responsividade

- Mobile: painel ocupa grande parte da largura (ex.: 92vw) e altura controlada (ex.: 70vh).
- Desktop: painel com largura fixa confortável (ex.: 420–520px) e altura (ex.: 520–640px).

## Modelo de estado (Livewire)

O componente Livewire deve expor e controlar:

- `public bool $isOpen = false;`
- `public string $input = '';`
- `public array $messages = [];`
  - Cada item: `{ role: 'user'|'assistant'|'system', content: string, at?: string }`
- `public bool $isSending = false;`
- `public ?string $error = null;`

Comportamentos:

- `open()`, `close()`, `toggle()`
- `send()`: valida input, adiciona mensagem do usuário, simula “processamento” e adiciona resposta mockada.
- `resetConversation()` (opcional): limpa mensagens.

## Mock de mensagens

Enquanto não houver integração com IA, o componente deve produzir respostas mockadas com as seguintes regras:

- Deve haver uma lista de respostas padrão (ex.: 10–20 frases) para demonstrar o fluxo.
- A resposta pode ser:
  - Uma escolha pseudo-aleatória da lista; ou
  - Um “echo” formatado da pergunta (ex.: “Entendi: {pergunta}”).
- Deve simular latência (ex.: ~300–900ms) para evidenciar estado de loading.
- Opcional: permitir simular erro para validar UX (ex.: um comando “/erro” no input que dispara mensagem amigável).

## Integração futura com IA (fora do escopo)

Quando for implementada a integração real, ela deve ser encapsulada em uma camada própria (Action/Service), para não acoplar o componente a HTTP/SDK/credenciais.

Requisitos futuros esperados:

- Timeouts e exceções convertidos em mensagem de erro amigável no UI.
- Não registrar prompts/respostas contendo dados sensíveis em logs por padrão.

## Inclusão no app

O componente deve ser facilmente renderizável em Blade, com uma única chamada.

Requisitos:

- Deve existir um ponto único de inclusão (layout global do painel ou view compartilhada).
- A inclusão deve permitir desligar via config/feature flag se necessário.

## Segurança e conformidade

- Nunca expor chaves/segredos no frontend (quando houver integração real).
- Sanitizar saída do assistente:
  - Renderizar como texto (sem HTML) por padrão.
  - Se houver suporte futuro a Markdown, aplicar sanitizer estrito.
- Restringir acesso ao chat ao menos aos usuários autenticados do painel.

## Observabilidade

- Exibir erros no UI (banner discreto/notification), sem stack traces.
- Opcional: disparar evento interno (Livewire/JS) para telemetria de “mensagem enviada” e “erro”.

## Critérios de aceitação

- FAB aparece e fica fixo no viewport.
- Clique abre o painel e coloca foco no input.
- Enviar adiciona mensagem do usuário e depois a resposta do assistente.
- Durante o envio, UI mostra estado “processando” e bloqueia reenvio.
- Erros de backend aparecem de forma amigável e não quebram o componente.
- O componente pode ser incluído em uma view global do painel sem dependências extras.
