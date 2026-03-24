<?php

namespace App\Services;

use App\Events\TaskCreated;
use App\Events\TaskDeleted;
use App\Events\TaskUpdated;
use App\Events\TasksReordered;
use App\Exceptions\TaskException;
use App\Models\Task;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Collection;
use Exception;

class TaskService
{
    public function create(array $data)
    {
        try {
            return DB::transaction(function () use ($data) {
                $maxPriority = Task::where('project_id', $data['project_id'] ?? null)
                    ->max('priority') ?? 0;

                $data['priority'] = $maxPriority + 1;
                $data['user_id'] = auth()->id();

                $task = Task::create($data);
                event(new TaskCreated($task));
                return $task;
            });
        } catch (Exception $e) {
            Log::error('Task creation failed', ['error' => $e->getMessage(), 'data' => $data]);
            throw TaskException::createFailed($e->getMessage());
        }
    }

    public function update(Task $task, array $data)
    {
        try {
            return DB::transaction(function () use ($task, $data) {
                $task->update($data);
                event(new TaskUpdated($task));
                return $task;
            });
        } catch (Exception $e) {
            Log::error('Task update failed', ['task_id' => $task->id, 'error' => $e->getMessage()]);
            throw TaskException::updateFailed($e->getMessage());
        }
    }

    public function delete(Task $task)
    {
        try {
            $task->delete();
            event(new TaskDeleted($task));
            Log::info('Task deleted', ['task_id' => $task->id]);
        } catch (Exception $e) {
            Log::error('Task deletion failed', ['task_id' => $task->id, 'error' => $e->getMessage()]);
            throw TaskException::deleteFailed($e->getMessage());
        }
    }

    public function reorder(int $projectId, array $taskIds)
    {
        try {
            return DB::transaction(function () use ($projectId, $taskIds) {
                foreach ($taskIds as $index => $taskId) {
                    Task::where('id', $taskId)
                        ->where('project_id', $projectId)
                        ->update(['priority' => $index + 1]);
                }
                event(new TasksReordered($projectId));
                Log::info('Tasks reordered', ['project_id' => $projectId]);
            });
        } catch (Exception $e) {
            Log::error('Reorder failed', ['project_id' => $projectId, 'error' => $e->getMessage()]);
            throw TaskException::reorderFailed();
        }
    }

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
        } catch (Exception $e) {
            Log::error('TaskService::getTasks failed', [
                'project_id' => $projectId,
                'error'      => $e->getMessage(),
                'trace'      => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}
