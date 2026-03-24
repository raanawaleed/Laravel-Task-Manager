<?php

namespace App\Services;

use App\Events\TaskCreated;
use App\Events\TaskDeleted;
use App\Events\TaskUpdated;
use App\Events\TasksReordered;
use App\Exceptions\TaskException;
use App\Models\Task;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TaskService
{
    /**
     * Retrieve all tasks, optionally filtered by project.
     */
    public function getTasks(?int $projectId = null): Collection
    {
        try {
            $query = Task::with('project')->orderBy('priority');
            if ($projectId !== null) {
                $query->where('project_id', $projectId);
            }
            return $query->get();
        } catch (\Throwable $e) {
            Log::error('TaskService::getTasks failed', [
                'project_id' => $projectId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Create a new task and assign the next available priority.
     */
    public function createTask(array $data): Task
    {
        try {
            return DB::transaction(function () use ($data) {
                $query = Task::query();
                if (!empty($data['project_id'])) {
                    $query->where('project_id', $data['project_id']);
                } else {
                    $query->whereNull('project_id');
                }

                $data['priority'] = ($query->max('priority') ?? 0) + 1;

                $task = Task::create($data);

                event(new TaskCreated($task));
                Log::info('Task created', ['task_id' => $task->id, 'name' => $task->name]);

                return $task;
            });
        } catch (\Throwable $e) {
            Log::error('TaskService::createTask failed', [
                'data' => $data,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw TaskException::createFailed($e->getMessage());
        }
    }

    /**
     * Update an existing task.
     */
    public function updateTask(Task $task, array $data): Task
    {
        try {
            return DB::transaction(function () use ($task, $data) {
                $task->update($data);

                event(new TaskUpdated($task));
                Log::info('Task updated', ['task_id' => $task->id]);

                return $task->fresh();
            });
        } catch (\Throwable $e) {
            Log::error('TaskService::updateTask failed', [
                'task_id' => $task->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw TaskException::updateFailed($e->getMessage());
        }
    }

    /**
     * Delete a task and re-sequence remaining priorities.
     */
    public function deleteTask(Task $task): bool
    {
        try {
            return DB::transaction(function () use ($task) {
                $taskId = $task->id;
                $taskName = $task->name;
                $projectId = $task->project_id;

                $task->delete();
                $this->resequencePriorities($projectId);

                event(new TaskDeleted($taskId, $taskName));
                Log::info('Task deleted', ['task_id' => $taskId]);

                return true;
            });
        } catch (\Throwable $e) {
            Log::error('TaskService::deleteTask failed', [
                'task_id' => $task->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw TaskException::deleteFailed($e->getMessage());
        }
    }

    /**
     * Reorder tasks based on a provided ordered array of IDs.
     */
    public function reorderTasks(array $taskIds): bool
    {
        try {
            return DB::transaction(function () use ($taskIds) {
                foreach ($taskIds as $index => $taskId) {
                    Task::where('id', $taskId)->update(['priority' => $index + 1]);
                }

                event(new TasksReordered($taskIds));
                Log::info('Tasks reordered', ['task_ids' => $taskIds]);

                return true;
            });
        } catch (\Throwable $e) {
            Log::error('TaskService::reorderTasks failed', [
                'task_ids' => $taskIds,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw TaskException::reorderFailed();
        }
    }

    /**
     * Re-sequence task priorities sequentially (1, 2, 3...) after a deletion.
     */
    private function resequencePriorities(?int $projectId): void
    {
        $query = Task::query()->orderBy('priority');
        if ($projectId !== null) {
            $query->where('project_id', $projectId);
        } else {
            $query->whereNull('project_id');
        }

        foreach ($query->get() as $index => $task) {
            $task->update(['priority' => $index + 1]);
        }
    }
}
