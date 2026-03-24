<?php

namespace App\Listeners;

use App\Events\TaskCreated;
use Illuminate\Support\Facades\Log;

class LogTaskCreated
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(TaskCreated $event): void
    {
        Log::info('[Event] Task Created', [
            'task_id'    => $event->task->id,
            'task_name'  => $event->task->name,
            'priority'   => $event->task->priority,
            'project_id' => $event->task->project_id,
            'timestamp'  => now()->toISOString(),
        ]);
    }
}
