<?php

namespace App\Http\Controllers;

use App\Facades\ProjectFacade;
use App\Facades\TaskFacade;
use App\Http\Requests\ReorderTasksRequest;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Task::class);

        try {
            $projectId = $request->filled('project_id') ? (int) $request->get('project_id') : null;
            $tasks     = TaskFacade::getTasks($projectId);
            $projects  = ProjectFacade::getAllProjects();

            return view('tasks.index', compact('tasks', 'projects', 'projectId'));
        } catch (\Throwable $e) {
            Log::error('TaskController::index failed', ['error' => $e->getMessage()]);
            return back()->withErrors(['error' => 'Unable to load tasks.']);
        }
    }

    public function create()
    {
        $this->authorize('create', Task::class);

        try {
            $projects = ProjectFacade::getAllProjects();
            return view('tasks.create', compact('projects'));
        } catch (\Throwable $e) {
            Log::error('TaskController::create failed', ['error' => $e->getMessage()]);
            return back()->withErrors(['error' => 'Unable to load form.']);
        }
    }

    public function store(StoreTaskRequest $request)
    {
        $this->authorize('create', Task::class);

        try {
            $task = TaskFacade::createTask($request->validated());

            return redirect()->route('tasks.index')
                ->with('success', "Task '{$task->name}' created successfully.");
        } catch (\Throwable $e) {
            Log::error('TaskController::store failed', ['error' => $e->getMessage()]);
            return back()->withErrors(['error' => 'Failed to create task.'])->withInput();
        }
    }

    public function edit(Task $task)
    {
        $this->authorize('update', $task);

        try {
            $projects = ProjectFacade::getAllProjects();
            return view('tasks.edit', compact('task', 'projects'));
        } catch (\Throwable $e) {
            Log::error('TaskController::edit failed', [
                'task_id' => $task->id,
                'error'   => $e->getMessage(),
            ]);
            return back()->withErrors(['error' => 'Unable to load form.']);
        }
    }

    public function update(UpdateTaskRequest $request, Task $task)
    {
        $this->authorize('update', $task);

        try {
            $updated = TaskFacade::updateTask($task, $request->validated());

            return redirect()->route('tasks.index')
                ->with('success', "Task '{$updated->name}' updated successfully.");
        } catch (\Throwable $e) {
            Log::error('TaskController::update failed', [
                'task_id' => $task->id,
                'error'   => $e->getMessage(),
            ]);
            return back()->withErrors(['error' => 'Failed to update task.'])->withInput();
        }
    }

    public function destroy(Task $task)
    {
        $this->authorize('delete', $task);

        try {
            TaskFacade::deleteTask($task);

            return redirect()->route('tasks.index')
                ->with('success', 'Task deleted successfully.');
        } catch (\Throwable $e) {
            Log::error('TaskController::destroy failed', [
                'task_id' => $task->id,
                'error'   => $e->getMessage(),
            ]);
            return back()->withErrors(['error' => 'Failed to delete task.']);
        }
    }

    public function reorder(ReorderTasksRequest $request)
    {
        $this->authorize('reorder', Task::class);

        try {
            TaskFacade::reorderTasks($request->validated()['task_ids']);

            return response()->json([
                'success' => true,
                'message' => 'Tasks reordered successfully.',
            ]);
        } catch (\Throwable $e) {
            Log::error('TaskController::reorder failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to reorder tasks.',
            ], 500);
        }
    }
}
