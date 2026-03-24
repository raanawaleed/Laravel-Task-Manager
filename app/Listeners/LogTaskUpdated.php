<?php

namespace App\Listeners;

use App\Events\TaskUpdated;
use Illuminate\Support\Facades\Log;

class LogTaskUpdated
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
    public function handle(TaskUpdated $event): void
    {
        Log::info('[Event] Task Updated', [
            'task_id'   => $event->task->id,
            'task_name' => $event->task->name,
            'timestamp' => now()->toISOString(),
        ]);
    }
}
