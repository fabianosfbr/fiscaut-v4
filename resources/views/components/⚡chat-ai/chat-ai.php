<?php

use Illuminate\Support\Str;
use Livewire\Component;

new class extends Component
{
    public bool $isOpen = false;

    public string $input = '';

    public array $messages = [];

    public bool $isSending = false;

    public ?string $error = null;

    public function mount(): void
    {
        $this->messages = [
            [
                'role' => 'assistant',
                'content' => 'Olá! Eu sou o assistente do Fiscaut. Por enquanto, minhas respostas são simuladas.',
                'at' => now()->toIso8601String(),
            ],
        ];
    }

    public function open(): void
    {
        $this->isOpen = true;
    }

    public function close(): void
    {
        $this->isOpen = false;
    }

    public function toggle(): void
    {
        $this->isOpen = ! $this->isOpen;
    }

    public function resetConversation(): void
    {
        $this->error = null;
        $this->input = '';

        $this->messages = [
            [
                'role' => 'assistant',
                'content' => 'Conversa reiniciada. Me diga como posso ajudar.',
                'at' => now()->toIso8601String(),
            ],
        ];
    }

    public function send(): void
    {
        if ($this->isSending) {
            return;
        }

        $this->error = null;

        $message = Str::of($this->input)->trim()->toString();

        if ($message === '') {
            return;
        }

        if (mb_strlen($message) > 2000) {
            $this->error = 'Sua mensagem é muito longa.';

            return;
        }

        $this->isSending = true;

        $this->appendMessage('user', $message);

        $this->input = '';

        try {
            $assistant = $this->mockAssistantResponse($message);
            $this->appendMessage('assistant', $assistant);
        } catch (Throwable $e) {
            $this->error = 'Não foi possível gerar uma resposta agora. Tente novamente.';
        } finally {
            $this->isSending = false;
        }
    }

    private function appendMessage(string $role, string $content): void
    {
        $this->messages[] = [
            'role' => $role,
            'content' => $content,
            'at' => now()->toIso8601String(),
        ];
    }

    private function mockAssistantResponse(string $userMessage): string
    {
        if (Str::startsWith($userMessage, '/erro')) {
            throw new RuntimeException('Mock error');
        }

        usleep(random_int(300_000, 900_000));

        $responses = [
            'Certo. Posso ajudar com isso. O que você já tentou?',
            'Entendi. Quer que eu detalhe o passo a passo?',
            'Boa pergunta. Você pode me dizer em qual tela isso acontece?',
            'Ok. Qual é o objetivo final (relatório, cadastro, importação, etc.)?',
            'Anotado. Se você me passar um exemplo, consigo orientar melhor.',
            'Vamos por partes. Qual é a regra fiscal envolvida?',
            'Entendi: “%s”. Se quiser, posso sugerir uma validação para isso.',
            'Perfeito. Você quer que eu explique em termos técnicos ou de negócio?',
            'Posso te ajudar a depurar isso. Há alguma mensagem de erro?',
        ];

        $pick = $responses[random_int(0, count($responses) - 1)];

        if (str_contains($pick, '%s')) {
            return sprintf($pick, $userMessage);
        }

        return $pick;
    }
};
