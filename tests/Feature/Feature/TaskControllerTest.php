<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_guests_cannot_access_tasks(): void
    {
        $this->get(route('tasks.index'))->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_task_list(): void
    {
        Task::factory()->count(3)->create();

        $this->actingAs($this->user)
            ->get(route('tasks.index'))
            ->assertOk()
            ->assertViewIs('tasks.index')
            ->assertViewHas('tasks');
    }

    public function test_task_list_can_be_filtered_by_project(): void
    {
        $project = Project::factory()->create();
        Task::factory()->create(['project_id' => $project->id]);
        Task::factory()->create(['project_id' => null]);

        $this->actingAs($this->user)
            ->get(route('tasks.index', ['project_id' => $project->id]))
            ->assertOk()
            ->assertViewHas('tasks', fn($tasks) => $tasks->count() === 1);
    }

    public function test_can_view_create_task_form(): void
    {
        $this->actingAs($this->user)
            ->get(route('tasks.create'))
            ->assertOk()
            ->assertViewIs('tasks.create');
    }

    public function test_can_create_a_task(): void
    {
        $this->actingAs($this->user)
            ->post(route('tasks.store'), ['name' => 'New Task', 'project_id' => null])
            ->assertRedirect(route('tasks.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('tasks', ['name' => 'New Task', 'priority' => 1]);
    }

    public function test_create_task_validates_name_required(): void
    {
        $this->actingAs($this->user)
            ->post(route('tasks.store'), ['name' => '', 'project_id' => null])
            ->assertSessionHasErrors('name');
    }

    public function test_create_task_validates_project_exists(): void
    {
        $this->actingAs($this->user)
            ->post(route('tasks.store'), ['name' => 'Task', 'project_id' => 9999])
            ->assertSessionHasErrors('project_id');
    }

    public function test_can_view_edit_task_form(): void
    {
        $task = Task::factory()->create();

        $this->actingAs($this->user)
            ->get(route('tasks.edit', $task))
            ->assertOk()
            ->assertViewIs('tasks.edit')
            ->assertViewHas('task', $task);
    }

    public function test_can_update_a_task(): void
    {
        $task = Task::factory()->create(['name' => 'Old Name']);

        $this->actingAs($this->user)
            ->put(route('tasks.update', $task), ['name' => 'New Name', 'project_id' => null])
            ->assertRedirect(route('tasks.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('tasks', ['id' => $task->id, 'name' => 'New Name']);
    }

    public function test_can_delete_a_task(): void
    {
        $task = Task::factory()->create();

        $this->actingAs($this->user)
            ->delete(route('tasks.destroy', $task))
            ->assertRedirect(route('tasks.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }

    public function test_can_reorder_tasks(): void
    {
        $t1 = Task::factory()->create(['priority' => 1]);
        $t2 = Task::factory()->create(['priority' => 2]);
        $t3 = Task::factory()->create(['priority' => 3]);

        $this->actingAs($this->user)
            ->postJson(route('tasks.reorder'), ['task_ids' => [$t3->id, $t1->id, $t2->id]])
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertEquals(1, $t3->fresh()->priority);
        $this->assertEquals(2, $t1->fresh()->priority);
        $this->assertEquals(3, $t2->fresh()->priority);
    }

    public function test_reorder_validates_task_ids_required(): void
    {
        $this->actingAs($this->user)
            ->postJson(route('tasks.reorder'), ['task_ids' => []])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('task_ids');
    }

    public function test_reorder_validates_task_ids_must_exist(): void
    {
        $this->actingAs($this->user)
            ->postJson(route('tasks.reorder'), ['task_ids' => [99999]])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('task_ids.0');
    }
}
