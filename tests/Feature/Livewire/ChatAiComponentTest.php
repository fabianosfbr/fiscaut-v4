<?php

namespace Tests\Feature\Livewire;

use Livewire\Livewire;
use Tests\TestCase;

class ChatAiComponentTest extends TestCase
{
    public function test_send_appends_user_and_assistant_messages(): void
    {
        $component = Livewire::test('chat-ai')
            ->set('input', 'Oi')
            ->call('send')
            ->assertSet('input', '')
            ->assertSet('error', null);

        $messages = $component->get('messages');

        $this->assertIsArray($messages);
        $this->assertGreaterThanOrEqual(3, count($messages));

        $this->assertSame('user', $messages[count($messages) - 2]['role'] ?? null);
        $this->assertSame('Oi', $messages[count($messages) - 2]['content'] ?? null);

        $this->assertSame('assistant', $messages[count($messages) - 1]['role'] ?? null);
        $this->assertNotSame('', (string) ($messages[count($messages) - 1]['content'] ?? ''));
    }

    public function test_send_sets_error_on_mock_failure(): void
    {
        $component = Livewire::test('chat-ai')
            ->set('input', '/erro')
            ->call('send')
            ->assertSet('input', '')
            ->assertSet('error', 'Não foi possível gerar uma resposta agora. Tente novamente.');

        $messages = $component->get('messages');

        $this->assertIsArray($messages);
        $this->assertGreaterThanOrEqual(2, count($messages));
        $this->assertSame('user', $messages[count($messages) - 1]['role'] ?? null);
        $this->assertSame('/erro', $messages[count($messages) - 1]['content'] ?? null);
    }
}
