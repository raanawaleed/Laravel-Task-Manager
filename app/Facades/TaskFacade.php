<?php

namespace App\Facades;

use App\Models\Task;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Facade;

/**
 * @method static Collection getTasks(?int $projectId = null)
 * @method static Task       createTask(array $data)
 * @method static Task       updateTask(Task $task, array $data)
 * @method static bool       deleteTask(Task $task)
 * @method static bool       reorderTasks(array $taskIds)
 *
 * @see \App\Services\TaskService
 */
class TaskFacade extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'task.service';
    }
}
