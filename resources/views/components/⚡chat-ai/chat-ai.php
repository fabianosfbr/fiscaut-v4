<?php

use App\AppNeuronFiscautAgent;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use NeuronAI\Chat\History\EloquentChatHistory;
use NeuronAI\Chat\Messages\UserMessage;
use NeuronAI\Laravel\Models\ChatMessage;

new class extends Component
{
    public bool $isOpen = false;

    public string $input = '';

    public array $messages = [];

    public bool $isSending = false;

    public ?string $error = null;

    public function mount(): void
    {
        $this->loadHistory();
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

        ChatMessage::where('thread_id', $this->getThreadId())->delete();
        $this->messages = [$this->defaultAssistantMessage()];
    }

    protected function getThreadId(): string
    {
        $issuerId = Auth::user()?->currentIssuer?->id ?? 'no_issuer';
        $userId = Auth::id() ?? 'guest';

        return "issuer_{$issuerId}_user_{$userId}";
    }

    public function send(): void
    {
        if ($this->isSending) {
            return;
        }

        $this->error = null;

        $text = trim($this->input);

        if ($text === '') {
            return;
        }

        if (mb_strlen($text) > 2000) {
            $this->error = 'Sua mensagem é muito longa.';

            return;
        }

        $this->isSending = true;

        $this->appendMessage('user', $text);

        $this->input = '';

        try {
            $chatHistory = new EloquentChatHistory(
                threadId: $this->getThreadId(),
                modelClass: ChatMessage::class,
            );

            $agent = new AppNeuronFiscautAgent;
            $agent->setChatHistory($chatHistory);

            $response = $agent->chat(new UserMessage($text))->getMessage()->getContent();

            $this->appendMessage('assistant', $this->normalizeMessageContent($response));

        } catch (\Throwable) {
            $this->appendMessage(
                'assistant',
                'Desculpe, ocorreu um erro ao processar sua mensagem. Tente novamente.'
            );
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

    protected function loadHistory(): void
    {
        $records = ChatMessage::where('thread_id', $this->getThreadId())
            ->whereIn('role', ['user', 'assistant'])
            ->orderBy('id')
            ->get(['role', 'content', 'created_at']);

        if ($records->isEmpty()) {
            $this->messages = [$this->defaultAssistantMessage()];

            return;
        }

        $this->messages = $records->map(function (ChatMessage $record) {
            return [
                'role' => $record->role,
                'content' => $this->extractTextContent($record->content),
                'at' => $record->created_at?->toIso8601String(),
            ];
        })->all();
    }

    private function extractTextContent(mixed $content): string
    {
        return $this->normalizeMessageContent($content);
    }

    private function normalizeMessageContent(mixed $content): string
    {
        if (is_string($content)) {
            $decoded = json_decode($content, true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $this->normalizeMessageContent($decoded);
            }

            return $content;
        }

        if (! is_array($content)) {
            return (string) $content;
        }

        // Format: ['type' => 'text', 'content' => '...']
        if (($content['type'] ?? null) === 'text' && isset($content['content'])) {
            return (string) $content['content'];
        }

        // Format: ['content' => [ ...blocks... ]]
        if (isset($content['content']) && is_array($content['content'])) {
            return $this->normalizeMessageContent($content['content']);
        }

        $texts = collect($content)
            ->filter(fn (mixed $block): bool => is_array($block) && ($block['type'] ?? null) === 'text')
            ->map(fn (array $block): string => (string) ($block['content'] ?? ''))
            ->filter()
            ->values()
            ->all();

        if ($texts !== []) {
            return implode("\n", $texts);
        }

        return json_encode($content, JSON_UNESCAPED_UNICODE) ?: '';
    }

    private function defaultAssistantMessage(): array
    {
        return [
            'role' => 'assistant',
            'content' => 'Olá! Eu sou o assistente do Fiscaut. Como posso te ajudar hoje?',
            'at' => now()->toIso8601String(),
        ];
    }
};
