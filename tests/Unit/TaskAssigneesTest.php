<?php

namespace Tests\Unit;

use App\Enums\TaskStatusEnum;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskAssigneesTest extends TestCase
{
    use RefreshDatabase;

    public function test_task_can_have_multiple_assignees(): void
    {
        $task = Task::create([
            'title' => 'Organizar onboarding',
            'description' => 'Preparar documentos e checklists',
            'urgent' => false,
            'project' => 'Condomínio Alfa',
            'due_date' => now()->addDay(),
            'progress' => 20,
            'status' => TaskStatusEnum::TODO,
            'order_column' => 1,
        ]);

        $users = User::factory()->count(3)->create();

        $task->assignees()->sync($users->pluck('id')->all());

        $this->assertCount(3, $task->fresh()->assignees);
        $this->assertSame(
            $users->pluck('id')->sort()->values()->all(),
            $task->fresh()->assignees->pluck('id')->sort()->values()->all(),
        );
    }

    public function test_user_avatar_initials_are_generated_from_name(): void
    {
        $user = User::factory()->create([
            'name' => 'Maria Clara',
        ]);

        $this->assertSame('MC', $user->getAvatarInitials());
        $this->assertNotEmpty($user->getAvatarBackgroundColor());
    }
}
