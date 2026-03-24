<?php

namespace App\Listeners;

use App\Events\TaskDeleted;
use Illuminate\Support\Facades\Log;

class LogTaskDeleted
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
    public function handle(TaskDeleted $event): void
    {
        Log::info('[Event] Task Deleted', [
            'task_id'   => $event->taskId,
            'task_name' => $event->taskName,
            'timestamp' => now()->toISOString(),
        ]);
    }
}
