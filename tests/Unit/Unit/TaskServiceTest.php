<?php

namespace Tests\Unit;

use App\Events\TaskCreated;
use App\Events\TaskDeleted;
use App\Events\TasksReordered;
use App\Events\TaskUpdated;
use App\Models\Project;
use App\Models\Task;
use App\Services\TaskService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class TaskServiceTest extends TestCase
{
    use RefreshDatabase;

    private TaskService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TaskService();
    }

    public function test_get_all_tasks_returns_collection(): void
    {
        Task::factory()->count(3)->create();

        $tasks = $this->service->getTasks();

        $this->assertCount(3, $tasks);
    }

    public function test_get_tasks_filtered_by_project(): void
    {
        $project = Project::factory()->create();
        Task::factory()->count(2)->create(['project_id' => $project->id]);
        Task::factory()->count(3)->create(['project_id' => null]);

        $tasks = $this->service->getTasks($project->id);

        $this->assertCount(2, $tasks);
        $tasks->each(fn($t) => $this->assertEquals($project->id, $t->project_id));
    }

    public function test_create_task_saves_to_database(): void
    {
        Event::fake([TaskCreated::class]);

        $task = $this->service->createTask(['name' => 'My Task', 'project_id' => null]);

        $this->assertDatabaseHas('tasks', ['name' => 'My Task', 'priority' => 1]);
        Event::assertDispatched(TaskCreated::class);
    }

    public function test_create_task_priority_auto_increments(): void
    {
        Event::fake();

        $this->service->createTask(['name' => 'Task 1', 'project_id' => null]);
        $this->service->createTask(['name' => 'Task 2', 'project_id' => null]);
        $third = $this->service->createTask(['name' => 'Task 3', 'project_id' => null]);

        $this->assertEquals(3, $third->priority);
    }

    public function test_create_task_priority_is_project_scoped(): void
    {
        Event::fake();

        $p1 = Project::factory()->create();
        $p2 = Project::factory()->create();

        $this->service->createTask(['name' => 'P1 Task 1', 'project_id' => $p1->id]);
        $this->service->createTask(['name' => 'P1 Task 2', 'project_id' => $p1->id]);
        $p2task = $this->service->createTask(['name' => 'P2 Task 1', 'project_id' => $p2->id]);

        $this->assertEquals(1, $p2task->priority);
    }

    public function test_update_task_persists_changes(): void
    {
        Event::fake([TaskUpdated::class]);

        $task    = Task::factory()->create(['name' => 'Original']);
        $updated = $this->service->updateTask($task, ['name' => 'Updated', 'project_id' => null]);

        $this->assertEquals('Updated', $updated->name);
        Event::assertDispatched(TaskUpdated::class);
    }

    public function test_delete_task_removes_from_database(): void
    {
        Event::fake([TaskDeleted::class]);

        $task   = Task::factory()->create();
        $taskId = $task->id;

        $result = $this->service->deleteTask($task);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('tasks', ['id' => $taskId]);
        Event::assertDispatched(TaskDeleted::class);
    }

    public function test_priorities_resequenced_after_delete(): void
    {
        Event::fake();

        $t1 = $this->service->createTask(['name' => 'Task 1', 'project_id' => null]);
        $t2 = $this->service->createTask(['name' => 'Task 2', 'project_id' => null]);
        $t3 = $this->service->createTask(['name' => 'Task 3', 'project_id' => null]);

        $this->service->deleteTask($t2);

        $this->assertEquals(1, $t1->fresh()->priority);
        $this->assertEquals(2, $t3->fresh()->priority);
    }

    public function test_reorder_tasks_updates_priorities(): void
    {
        Event::fake([TasksReordered::class]);

        $t1 = Task::factory()->create(['priority' => 1]);
        $t2 = Task::factory()->create(['priority' => 2]);
        $t3 = Task::factory()->create(['priority' => 3]);

        $this->service->reorderTasks([$t3->id, $t1->id, $t2->id]);

        $this->assertEquals(1, $t3->fresh()->priority);
        $this->assertEquals(2, $t1->fresh()->priority);
        $this->assertEquals(3, $t2->fresh()->priority);

        Event::assertDispatched(TasksReordered::class);
    }
}
